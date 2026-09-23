<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\QaThreadStatus;
use App\Enums\UserRole;
use App\Models\Certification;
use App\Models\QaThread;
use App\Models\User;
use Illuminate\Database\Seeder;

class QaThreadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $student = User::where('role', UserRole::Student)->firstOrFail();

        $certifications = Certification::query()
            ->published()->get();

        foreach ($certifications as $certification) {

            $count = rand(1, 3);
            for ($i = 0; $i < $count; $i++) {
                QaThread::factory()->create([
                    'user_id' => $student->id,
                    'certification_id' => $certification->id,
                    'status' => QaThreadStatus::Unresolved,
                    'resolved_at' => null,
                    'created_at' => now()->subDays(rand(5, 10)),
                ]);
            }
            $count = rand(1, 3);
            for ($i = 0; $i < $count; $i++) {
                QaThread::factory()->create([
                    'user_id' => $student->id,
                    'certification_id' => $certification->id,
                    'status' => QaThreadStatus::Resolved,
                    'resolved_at' => now(),
                    'created_at' => now()->subDays(rand(5, 10)),
                ]);
            }

        }
    }
}
