<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\GoogleCredential;
use App\Models\User;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;
use Google\Client;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleCalendarServiceTest extends TestCase
{
    use RefreshDatabase;

    // アクセストークンが期限切れの場合に更新され、保存されることを確認する
    public function test_refreshes_expired_access_token_and_saves_it(): void
    {
        // Arrange
        $credential = $this->createCredential([
            'access_token' => json_encode([
                'access_token' => 'old-token',
                'created' => 1,
                'expires_in' => 1,
            ], JSON_THROW_ON_ERROR),
            'refresh_token' => 'refresh-token',
        ]);

        $service = $this->createService([
            new Response(200, [], json_encode([
                'access_token' => 'new-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ], JSON_THROW_ON_ERROR)),
            new Response(200, [], json_encode([
                'calendars' => [
                    'primary' => [
                        'busy' => [],
                    ],
                ],
            ], JSON_THROW_ON_ERROR)),
        ]);

        // Act
        $result = $service->getBusyIntervals(
            $credential,
            Carbon::parse('2026-11-01 00:00:00'),
            Carbon::parse('2026-11-01 23:59:59'),
        );

        // Assert
        $this->assertSame([], $result);

        $credential->refresh();

        $savedToken = json_decode(
            $credential->access_token,
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $this->assertSame('new-token', $savedToken['access_token']);
        $this->assertNotNull($credential->token_expires_at);
    }

    // アクセストークンの更新に失敗した場合に例外が投げられることを確認する
    public function test_throws_exception_when_token_refresh_fails(): void
    {
        // Arrange
        $credential = $this->createCredential([
            'access_token' => json_encode([
                'access_token' => 'old-token',
                'created' => 1,
                'expires_in' => 1,
            ], JSON_THROW_ON_ERROR),
            'refresh_token' => 'invalid-refresh-token',
        ]);

        $service = $this->createService([
            new Response(400, [], json_encode([
                'error' => 'invalid_grant',
            ], JSON_THROW_ON_ERROR)),
        ]);

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Google カレンダーのアクセストークンを更新できませんでした。',
        );

        // Act
        $service->getBusyIntervals(
            $credential,
            Carbon::parse('2026-11-01 00:00:00'),
            Carbon::parse('2026-11-01 23:59:59'),
        );
    }

    // リフレッシュトークンが存在しない場合に例外が投げられることを確認する
    public function test_throws_exception_when_refresh_token_is_missing(): void
    {
        // Arrange
        $credential = $this->createCredential([
            'access_token' => json_encode([
                'access_token' => 'old-token',
                'created' => 1,
                'expires_in' => 1,
            ], JSON_THROW_ON_ERROR),
            'refresh_token' => null,
        ]);

        $service = $this->createService([]);

        // Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'Google カレンダーの再認証が必要です。',
        );

        // Act
        $service->getBusyIntervals(
            $credential,
            Carbon::parse('2026-11-01 00:00:00'),
            Carbon::parse('2026-11-01 23:59:59'),
        );
    }

    private function createCredential(array $attributes): GoogleCredential
    {
        return GoogleCredential::create(array_merge([
            'user_id' => User::factory()->create()->id,
            'google_user_id' => 'test-google-user',
            'calendar_id' => 'primary',
            'access_token' => '{}',
            'refresh_token' => 'test-refresh-token',
            'token_expires_at' => now()->subHour(),
            'connected_at' => now(),
        ], $attributes));
    }

    private function createService(array $responses): GoogleCalendarService
    {
        $handler = HandlerStack::create(
            new MockHandler($responses),
        );

        $httpClient = new HttpClient([
            'handler' => $handler,
        ]);

        $googleClient = new Client;
        $googleClient->setClientId('test-client-id');
        $googleClient->setClientSecret('test-client-secret');
        $googleClient->setRedirectUri('http://localhost/callback');
        $googleClient->setHttpClient($httpClient);

        return new TestGoogleCalendarService($googleClient);
    }
}

class TestGoogleCalendarService extends GoogleCalendarService
{
    public function __construct(
        private readonly Client $testClient,
    ) {}

    protected function createClient(): Client
    {
        return $this->testClient;
    }
}
