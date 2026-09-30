<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\EnrollmentGoal;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnrollmentGoalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $student = User::query()->where('role', UserRole::Student->value)->first();

        $texts = [
            'Laravelの理解を深める。',
            '実務で通用するWebアプリケーション開発力を身につける。',
            'テスト駆動開発（TDD）の手法を習得する。',
            '品質の高いコードを書けるようになる。',
            '周辺技術についても理解を深める。',
            '原因を特定して解決できる力を身につける。',
        ];
        if ($student) {
            $enrollments = $student->enrollments()->get();

            foreach ($enrollments as $enrollment) {
                for ($i = 0; $i < rand(4, 8); $i++) {
                    EnrollmentGoal::factory()->create([
                        'enrollment_id' => $enrollment->id,
                        'title' => fake()->randomElement($texts),
                        'description' => fake()->boolean() ? '説明です' : null,
                        'achieved_at' => fake()->boolean() ? now() : null,
                        'target_date' => fake()->boolean() ? now()->addDays(rand(1, 30)) : null,
                    ]);
                }
            }
        }

    }
}
