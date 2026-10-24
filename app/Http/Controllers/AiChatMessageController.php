<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AiChatMessageRole;
use App\Enums\AiChatMessageStatus;
use App\Http\Requests\AiChatMessage\StoreRequest;
use App\Models\AiChatConversation;
use App\Models\Section;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Throwable;

class AiChatMessageController extends Controller
{
    public function store(
        StoreRequest $request,
        AiChatConversation $conversation,
        GeminiService $geminiService,
    ): JsonResponse {

        $this->authorize('store', $conversation);

        $validated = $request->validated();

        return $this->sendMessage(
            $conversation,
            $validated['content'],
            $geminiService,
        );
    }

    /**
     * メッセージを保存し、Geminiの回答を取得する。
     */
    public function sendMessage(
        AiChatConversation $conversation,
        string $content,
        GeminiService $geminiService,
    ): JsonResponse {
        $userMessage = $conversation->messages()->create([
            'content' => $content,
            'role' => AiChatMessageRole::User,
            'status' => AiChatMessageStatus::Completed,
        ]);

        $startedAt = microtime(true);

        try {
            $context = null;

            // 会話に教材が紐づいている場合だけ教材情報を取得する
            if ($conversation->section_id !== null) {
                $section = Section::published()
                    ->findOrFail($conversation->section_id);

                $context = [
                    'title' => $section->title,
                    'description' => $section->description,
                    'body' => $section->body,
                ];
            }

            $answer = $geminiService->generate($content, $context);

            $assistantMessage = $conversation->messages()->create([
                'content' => $answer,
                'role' => AiChatMessageRole::Assistant,
                'status' => AiChatMessageStatus::Completed,
                'response_time_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            $assistantMessage = $conversation->messages()->create([
                'content' => 'AIの回答を取得できませんでした。時間をおいて再度お試しください。',
                'role' => AiChatMessageRole::Assistant,
                'status' => AiChatMessageStatus::Error,
                'error_detail' => 'Gemini API request failed.',
                'response_time_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);

            return response()->json([
                'user_message' => $userMessage,
                'assistant_message' => $assistantMessage,
                'conversation' => $conversation->fresh(),
                'upstream_status' => null,
            ], 502);
        }

        return response()->json([
            'user_message' => $userMessage,
            'assistant_message' => $assistantMessage,
            'conversation' => $conversation->fresh(),
        ]);
    }
}
