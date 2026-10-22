<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\GoogleCredential;
use App\Models\User;
use Illuminate\Database\Seeder;

class GoogleCredentialSeeder extends Seeder
{
    public function run(): void
    {
        // Google カレンダー連携コーチ
        $connectedCoach = User::query()->where('role', UserRole::Coach->value)->first();

        GoogleCredential::create([
            'user_id' => $connectedCoach->id,
            'google_user_id' => 'test-google-user-id',
            'calendar_id' => 'primary',
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'token_expires_at' => now()->addHour(),
            'connected_at' => now(),
        ]);

    }
}
