<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\EnrollmentNote;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnrollmentNoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = User::query()->where('role', 'student')->get();
        foreach ($students as $student) {

            $enrollments = $student->enrollments;
            foreach ($enrollments as $enrollment) {

                $assignedCoaches = $enrollment->certification->coaches;
                foreach ($assignedCoaches as $coach) {

                    EnrollmentNote::factory()->create([
                        'enrollment_id' => $enrollment->id,
                        'user_id' => $coach->id,
                        'body' => fake()->randomElement([
                            '質問への回答に時間がかかっていた。',
                            '○○の理解で少しつまずいている。',
                            '次回の面談で○○について確認する。',
                            '課題の提出状況を確認する。',
                        ]),
                    ]);

                }
            }
        }
    }
}
