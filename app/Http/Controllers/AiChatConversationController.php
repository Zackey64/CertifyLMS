<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AiChatConversation\StoreRequest;
use App\Http\Requests\AiChatConversation\UpdateRequest;
use App\Models\AiChatConversation;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AiChatConversationController extends Controller
{
    public function index(): View
    {
        $conversation = AiChatConversation::where('user_id', auth()->id())
            ->orderByDesc('last_message_at')->first();
        if (! $conversation) {
            return view('ai-chat.empty-state');
        }
        $conversation->load('messages');

        return view('ai-chat.show', compact('conversation'));
    }

    public function store(
        StoreRequest $request,
        AiChatMessageController $messageController,
        GeminiService $geminiService,
    ): RedirectResponse|JsonResponse {
        $validated = $request->validated();

        $conversation = $request->user()
            ->aiChatConversations()
            ->create([
                'section_id' => $validated['section_id'] ?? null,
                'title' => $validated['title'] ?? '新しい相談',
            ]);

        $message = trim($validated['message'] ?? '');

        if ($message !== '') {
            $messageController->sendMessage(
                $conversation,
                $message,
                $geminiService,
            );
        }

        if ($validated['source'] === 'widget') {
            return response()->json([
                'conversation' => $conversation->fresh(),
            ], 201);
        }

        return redirect()->route('ai-chat.conversations.show', compact('conversation'))
            ->with('success', '会話を作成しました');
    }

    public function show(AiChatConversation $conversation): View
    {
        $this->authorize('view', $conversation);

        $conversation->load('messages');

        return view('ai-chat.show', compact('conversation'));
    }

    public function update(AiChatConversation $conversation, UpdateRequest $request): RedirectResponse
    {
        $this->authorize('update', $conversation);

        $conversation->update($request->validated());

        return redirect()->route('ai-chat.conversations.show', compact('conversation'))
            ->with('success', '会話を更新しました');
    }

    public function destroy(AiChatConversation $conversation): RedirectResponse
    {
        $this->authorize('delete', $conversation);

        $conversation->delete();

        return redirect()->route('ai-chat.index')
            ->with('success', '会話を削除しました');
    }
}
