<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class GeminiServiceTest extends TestCase
{
    use RefreshDatabase;

    private GeminiService $geminiService;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'ai-chat.gemini.api_key' => 'test-api-key',
            'ai-chat.gemini.model' => 'gemini-test-model',
        ]);

        $this->geminiService = new GeminiService;
    }

    /**
     * GeminiService が正しくインスタンス化されることを確認する。
     */
    public function test_generates_answer_successfully(): void
    {
        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'テスト回答です。'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $answer = $this->geminiService->generate('テスト質問');

        $this->assertSame('テスト回答です。', $answer);

        Http::assertSent(function (Request $request): bool {
            return str_contains($request->url(), 'gemini-test-model:generateContent')
                && $request->hasHeader('x-goog-api-key', 'test-api-key')
                && $request->data()['contents'][0]['parts'][0]['text'] === 'テスト質問';
        });

        Http::assertSentCount(1);
    }

    /**
     * 通信エラー時に例外がスローされることを確認する。
     */
    public function test_throws_exception_when_communication_fails(): void
    {
        Http::fake(function () {
            throw new ConnectionException('Connection failed');
        });

        try {
            $this->geminiService->generate('テスト質問');
            $this->fail('通信エラーの例外が発生する必要があります。');
        } catch (ConnectionException) {
            $this->assertTrue(true);
        }
    }

    /**
     * レスポンスが空の場合に例外がスローされることを確認する。
     */
    public function test_throws_exception_when_response_is_empty(): void
    {
        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => '  '],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Geminiから有効な回答を取得できませんでした。'
        );

        $this->geminiService->generate('テスト質問');
    }

    /**
     * セクションコンテキストを含むプロンプトが送信されることを確認する。
     */
    public function test_sends_prompt_with_section_context(): void
    {
        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'テスト回答です。'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $context = [
            'title' => 'Laravel入門',
            'description' => 'Laravelの基本を学ぶ教材',
            'body' => 'LaravelはPHPのWebアプリケーションフレームワークです。',
        ];

        $this->geminiService->generate('Laravelとは何ですか？', $context);

        Http::assertSent(function (Request $request): bool {
            $prompt = $request->data()['contents'][0]['parts'][0]['text'] ?? '';

            return $prompt === implode("\n", [
                'あなたは教材の学習を支援するAIです。',
                '以下の教材情報を参考に、質問に回答してください。',
                '教材に記載されていないことは、記載されていないと明示してください。',
                '【教材タイトル】',
                'Laravel入門',
                '【教材の説明】',
                'Laravelの基本を学ぶ教材',
                '【教材本文】',
                'LaravelはPHPのWebアプリケーションフレームワークです。',
                '【質問】',
                'Laravelとは何ですか？',
            ]);
        });
    }

    /**
     * APIがエラーを返した場合に例外がスローされることを確認する。
     */
    public function test_throws_exception_when_api_returns_error(): void
    {
        Http::fake([
            '*' => Http::response([
                'error' => ['message' => 'Service unavailable'],
            ], 503),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('HTTPステータス: 503');

        $this->geminiService->generate('テスト質問');
    }

    /**
     * 一時的なエラーの後に再試行して成功することを確認する。
     */
    public function test_retries_after_temporary_error_and_succeeds(): void
    {
        Http::fake([
            '*' => Http::sequence()
                ->push(['error' => ['message' => 'Service unavailable']], 503)
                ->push([
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    ['text' => '再試行後の回答です。'],
                                ],
                            ],
                        ],
                    ],
                ], 200),
        ]);

        $answer = $this->geminiService->generate('テスト質問');

        $this->assertSame('再試行後の回答です。', $answer);

        Http::assertSentCount(2);
    }
}
