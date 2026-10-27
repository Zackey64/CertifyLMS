<?php

declare(strict_types=1);

namespace Tests\Feature\Http\GoogleCalendar;

use App\Models\GoogleCredential;
use App\Models\User;
use App\Services\GoogleCalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CallbackTest extends TestCase
{
    use RefreshDatabase;

    // state不一致
    public function test_callback_redirects_when_state_is_invalid(): void
    {
        $coach = User::factory()->coach()->create();

        $response = $this->actingAs($coach)
            ->withSession(['google_calendar_oauth_state' => 'test-state'])
            ->get(route('settings.google-calendar.callback', [
                'state' => 'invalid-state',
                'code' => 'test-code',
            ]));

        $response->assertRedirect(route('settings.availability.index'));
        $response->assertSessionHas('error');
    }

    // Google側で認証キャンセル
    public function test_callback_redirects_when_google_auth_is_cancelled(): void
    {
        $coach = User::factory()->coach()->create();

        $response = $this->actingAs($coach)
            ->withSession(['google_calendar_oauth_state' => 'test-state'])
            ->get(route('settings.google-calendar.callback', [
                'state' => 'test-state',
                'error' => 'access_denied',
            ]));

        $response->assertRedirect(route('settings.availability.index'));
        $response->assertSessionHas('error');
    }

    // 認証コードがない
    public function test_callback_redirects_when_code_is_missing(): void
    {
        $coach = User::factory()->coach()->create();

        $response = $this->actingAs($coach)
            ->withSession(['google_calendar_oauth_state' => 'test-state'])
            ->get(route('settings.google-calendar.callback', [
                'state' => 'test-state',
            ]));

        $response->assertRedirect(route('settings.availability.index'));
        $response->assertSessionHas('error');
    }

    /**
     * 正常に認証コードが処理され、Google カレンダーの認証情報が保存される
     *
     * @group external-api
     */
    public function test_callback_saves_google_calendar_credentials(): void
    {
        $coach = User::factory()->coach()->create();

        $token = [
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires_in' => 3600,
        ];

        $googleCalendarService = $this->mock(GoogleCalendarService::class);

        $googleCalendarService
            ->shouldReceive('fetchAccessTokenWithAuthCode')
            ->once()
            ->with('test-code')
            ->andReturn($token);

        $googleCalendarService
            ->shouldReceive('getGoogleUserId')
            ->once()
            ->with($token)
            ->andReturn('google-user-123');

        $response = $this->actingAs($coach)
            ->withSession(['google_calendar_oauth_state' => 'test-state'])
            ->get(route('settings.google-calendar.callback', [
                'state' => 'test-state',
                'code' => 'test-code',
            ]));

        $response->assertRedirect(route('settings.availability.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('google_credentials', [
            'user_id' => $coach->id,
            'google_user_id' => 'google-user-123',
            'calendar_id' => 'primary',
            'refresh_token' => 'test-refresh-token',
        ]);

        $credential = GoogleCredential::where('user_id', $coach->id)->firstOrFail();

        $this->assertSame(
            'test-access-token',
            json_decode($credential->access_token, true)['access_token'],
        );

        $this->assertNotNull($credential->connected_at);
    }
}
