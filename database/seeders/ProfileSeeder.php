<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->student()->create([
            'name' => '受講生（アバター未設定）',
            'avatar_url' => null,
        ]);
        User::factory()->student()->create([
            'name' => '受講生（アバター設定済）',
            'avatar_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=1',
        ]);
        User::factory()->student()->create([
            'name' => '修了済み受講生（アバター未設定）',
            'status' => UserStatus::Graduated,
            'avatar_url' => null,
        ]);
        User::factory()->student()->create([
            'name' => '修了済み受講生（アバター設定済）',
            'status' => UserStatus::Graduated,
            'avatar_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=2',
        ]);
        User::factory()->coach()->create([
            'name' => 'コーチ（アバター未設定）',
            'meeting_url' => 'https://meet.google.com/xxx-yyyy-zzz',
            'avatar_url' => null,
        ]);
        User::factory()->coach()->create([
            'name' => 'コーチ（アバター設定済）',
            'meeting_url' => 'https://meet.google.com/xxx-yyyy-zzz',
            'avatar_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=3',
        ]);
        User::factory()->admin()->create([
            'name' => '管理者（アバター未設定）',
            'avatar_url' => null,
        ]);
        User::factory()->admin()->create([
            'name' => '管理者（アバター設定済）',
            'avatar_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=4',
        ]);
    }
}
