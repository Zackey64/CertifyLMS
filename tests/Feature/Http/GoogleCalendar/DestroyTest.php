<?php

declare(strict_types=1);

namespace Tests\Feature\Http\GoogleCalendar;

use App\Models\GoogleCredential;
use App\Models\User;
use App\Services\GoogleCalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    // Google カレンダーとの連携を解除できる。
    public function test_can_disconnect_google_calendar(): void
    {
        // Arrange
        $coach = User::factory()->coach()->create();
        GoogleCredential::create([
            'user_id' => $coach->id,
            'google_user_id' => 'google-user-123',
            'calendar_id' => 'primary',
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'token_expires_at' => now()->addHour(),
            'connected_at' => now(),
        ]);
        // Act
        $response = $this->actingAs($coach)->delete(route('settings.google-calendar.destroy'));
        // Assert
        $response->assertRedirect(route('settings.availability.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('google_credentials', [
            'user_id' => $coach->id,
        ]);
    }

    /**
     * Google API を呼び出さずに Google カレンダーとの連携を解除できる。
     *
     * @group external-api
     */
    public function test_can_disconnect_google_calendar_without_calling_google_api(): void
    {
        $coach = User::factory()->coach()->create();

        GoogleCredential::factory()->create([
            'user_id' => $coach->id,
        ]);

        $this->mock(GoogleCalendarService::class)
            ->shouldNotReceive('deleteMeetingEvent')
            ->shouldNotReceive('getBusyIntervals')
            ->shouldNotReceive('createMeetingEvent');

        $response = $this->actingAs($coach)
            ->delete(route('settings.google-calendar.destroy'));

        $response->assertRedirect(route('settings.availability.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('google_credentials', [
            'user_id' => $coach->id,
        ]);
    }
}
