<?php

declare(strict_types=1);

namespace Tests\Feature\Http\GoogleCalender;

use App\Models\User;
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
        $response->assertRedirectContains(
            'accounts.google.com'
        );
    }
}
