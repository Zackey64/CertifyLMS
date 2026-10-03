<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AnnouncementTargetType;
use App\Enums\UserRole;
use App\Http\Requests\Announcement\StoreRequest;
use App\Models\Announcement;
use App\Models\Certification;
use App\Models\User;
use App\Notifications\AnnouncementNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $announcements = Announcement::with([
            'createdBy',
            'targetCertification',
            'targetUser',
        ])->latest()->paginate(20);

        return view('announcement.management.index', compact('announcements'));
    }

    public function create(): View
    {
        $students = User::where('role', UserRole::Student->value)->get();
        $certifications = Certification::all();

        return view('announcement.management.create', compact('certifications', 'students'));
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        if ($validated['target_type'] === AnnouncementTargetType::AllStudents->value) {
            $validated['target_user_id'] = null;
            $validated['target_certification_id'] = null;
        }
        if ($validated['target_type'] === AnnouncementTargetType::Certification->value) {
            $validated['target_user_id'] = null;
        }
        if ($validated['target_type'] === AnnouncementTargetType::User->value) {
            $validated['target_certification_id'] = null;
        }
        $announcement = auth()->user()->announcements()->create($validated);

        // ここで通知送信処理を追加
        $users = match ($announcement->target_type) {
            AnnouncementTargetType::AllStudents => User::query()->where('role', UserRole::Student->value)->get(),

            AnnouncementTargetType::Certification => User::query()
                ->where('role', UserRole::Student->value)
                ->whereHas('enrollments', function ($query) use ($announcement) {
                    $query->where('certification_id', $announcement->target_certification_id);
                })
                ->get(),

            AnnouncementTargetType::User => User::query()->whereKey($announcement->target_user_id)->get(),
        };
        $announcement['dispatched_count'] = $users->count();
        $announcement['dispatched_at'] = now();
        $announcement->save();

        foreach ($users as $user) {
            $user->notify(new AnnouncementNotification($announcement));
        }

        return redirect()->route('admin.announcements.show', $announcement)
            ->with('success', 'お知らせを追加しました。');
    }

    public function show(Announcement $announcement): View
    {
        return view('announcement.management.show', compact('announcement'));
    }
}
