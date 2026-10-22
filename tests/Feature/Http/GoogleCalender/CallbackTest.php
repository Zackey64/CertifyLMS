<?php

declare(strict_types=1);

namespace Tests\Feature\Http\GoogleCalender;

use App\Models\User;
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

        $response->assertRedirect();
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

        $response->assertRedirect();
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

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
