<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\QaReply;
use App\Models\QaThread;
use App\Models\User;
use Illuminate\Database\Seeder;

class QaReplySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = User::query()->where('role', 'student')->get();
        $coaches = User::query()->where('role', 'coach')->get();
        $threads = QaThread::query()->get();

        foreach ($threads as $thread) {

            $count = rand(1, 5);
            for ($i = 0; $i < $count; $i++) {

                $isCoach = fake()->boolean();
                $users = $isCoach ? $coaches : $students;

                QaReply::factory()->create([
                    'user_id' => $users->random()->id,
                    'qa_thread_id' => $thread->id,
                ]);

            }

        }
    }
}
