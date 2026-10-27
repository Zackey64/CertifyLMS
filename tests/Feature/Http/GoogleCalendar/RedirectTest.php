<?php

declare(strict_types=1);

namespace Tests\Feature\Http\GoogleCalendar;

use App\Models\User;
use App\Services\GoogleCalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_redirect_to_google_calendar(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();

        // Act
        $response = $this->actingAs($coach)->get(route('settings.google-calendar.redirect'));

        // Assert
        $response->assertRedirect();
        $response->assertRedirectContains('accounts.google.com');
    }

    /**
     * Google カレンダーへのリダイレクト URL を生成できる。
     *
     * @group external-api
     */
    public function test_can_redirect_to_google_calendar_with_state(): void
    {
        $coach = User::factory()->coach()->create();

        $this->mock(GoogleCalendarService::class)
            ->shouldReceive('createAuthUrl')
            ->once()
            ->withArgs(fn (string $state) => strlen($state) === 64)
            ->andReturn('https://accounts.google.com/o/oauth2/auth?state=test-state');

        $response = $this->actingAs($coach)
            ->get(route('settings.google-calendar.redirect'));

        $response->assertRedirect(
            'https://accounts.google.com/o/oauth2/auth?state=test-state',
        );

        $response->assertSessionHas('google_calendar_oauth_state');
    }
}
