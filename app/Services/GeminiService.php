<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiService
{
    /**
     * Gemini APIにメッセージを送信し、回答を取得する。
     *
     * @throws ConnectionException
     * @throws RuntimeException
     */
    public function generate(
        string $message,
        ?array $context = null,
    ): string {
        $apiKey = config('ai-chat.gemini.api_key');
        $model = config('ai-chat.gemini.model');

        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('Gemini APIキーが設定されていません。');
        }

        $prompt = $message;

        if ($context !== null) {
            $prompt = implode("\n", [
                'あなたは教材の学習を支援するAIです。',
                '以下の教材情報を参考に、質問に回答してください。',
                '教材に記載されていないことは、記載されていないと明示してください。',
                '【教材タイトル】', $context['title'],
                '【教材の説明】', $context['description'] ?? '',
                '【教材本文】', $context['body'],
                '【質問】', $message,
            ]);
        }

        $response = Http::acceptJson()
            ->withHeaders([
                'x-goog-api-key' => $apiKey,
            ])
            ->connectTimeout(10)
            ->timeout(60)
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent",
                [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException(
                'Gemini APIの呼び出しに失敗しました。HTTPステータス: '
                .$response->status()
            );
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('Geminiから有効な回答を取得できませんでした。');
        }

        return $text;
    }
}
