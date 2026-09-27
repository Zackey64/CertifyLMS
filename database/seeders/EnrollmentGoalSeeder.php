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

        if ($student) {
            $enrollments = $student->enrollments()->get();

            foreach ($enrollments as $enrollment) {
                EnrollmentGoal::factory()->count(rand(4, 8))->create([
                    'enrollment_id' => $enrollment->id,
                    'achieved_at' => null,
                ]);
                EnrollmentGoal::factory()->count(rand(4, 8))->create([
                    'enrollment_id' => $enrollment->id,
                    'achieved_at' => now(),
                ]);
            }
        }
    }
}
