<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AnnouncementTargetType;
use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\Certification;
use App\Models\User;
use App\Notifications\AnnouncementNotification;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', UserRole::Admin->value)->firstOrFail();
        $students = User::where('role', UserRole::Student->value)->get();
        $certifications = Certification::all();

        // 全受講生向け
        $announcement = Announcement::factory()->create([
            'user_id' => $admin->id,
            'title' => '全受講生向けのお知らせ',
            'body' => 'これは全受講生向けのお知らせです。',
            'target_type' => AnnouncementTargetType::AllStudents->value,
            'target_certification_id' => null,
            'target_user_id' => null,
            'dispatched_count' => $students->count(),
            'dispatched_at' => now(),
        ]);
        $students->each(
            fn (User $student) => $student->notify(
                new AnnouncementNotification($announcement)
            )
        );

        // 各認定資格向け
        foreach ($certifications as $certification) {
            // 通知対象の受講生を取得
            $certificationStudents = User::query()->where('role', UserRole::Student->value)
                ->whereHas('enrollments', function ($query) use ($certification) {
                    $query->where('certification_id', $certification->id);
                })->get();
            $announcement = Announcement::factory()->create([
                'user_id' => $admin->id,
                'title' => $certification->name.'向けのお知らせ',
                'body' => 'これは'.$certification->name.'向けのお知らせです。',
                'target_type' => AnnouncementTargetType::Certification->value,
                'target_certification_id' => $certification->id,
                'target_user_id' => null,
                'dispatched_count' => $certificationStudents->count(),
                'dispatched_at' => now(),
            ]);
            // 通知配信
            $certificationStudents->each(
                fn (User $student) => $student->notify(
                    new AnnouncementNotification($announcement)
                )
            );
        }

        // 個別ユーザー向け
        foreach ($students as $student) {
            $announcement = Announcement::factory()->create([
                'user_id' => $admin->id,
                'title' => $student->name.'向けのお知らせ',
                'body' => 'これは'.$student->name.'向けのお知らせです。',
                'target_type' => AnnouncementTargetType::User->value,
                'target_certification_id' => null,
                'target_user_id' => $student->id,
                'dispatched_count' => 1,
                'dispatched_at' => now(),
            ]);
            $student->notify(
                new AnnouncementNotification($announcement)
            );
        }

    }
}
