<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\MeetingPackStatus;
use App\Http\Requests\MeetingPack\IndexRequest;
use App\Http\Requests\MeetingPack\StoreRequest;
use App\Models\MeetingPack;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MeetingPackController extends Controller
{
    public function index(IndexRequest $request): View
    {
        $this->authorize('viewAny', MeetingPack::class);
        $filters = $request->validated();

        $query = MeetingPack::query()->ordered(); // viewに$plan->payments_count

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['keyword'])) {
            $keyword = trim((string) $filters['keyword']);
            $query->where('name', 'like', "%{$keyword}%");
        }
        $plans = $query->paginate(10)->appends($filters);

        $keyword = $filters['keyword'] ?? null;
        $status = $filters['status'] ?? null;

        return view('meeting-pack.management.index', compact('plans', 'keyword', 'status', 'filters'));
    }

    public function create(): View
    {
        $this->authorize('create', MeetingPack::class);

        return view('meeting-pack.management.create');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $this->authorize('create', MeetingPack::class);
        $validated = $request->validated();

        $validated['status'] = MeetingPackStatus::Draft;
        $validated['created_by_user_id'] = auth()->id();
        $validated['updated_by_user_id'] = auth()->id();

        $plan = MeetingPack::create($validated);

        return redirect()->route('admin.meeting-packs.show', compact('plan'))
            ->with('success', '面談パックを作成しました。');
    }

    public function show(MeetingPack $plan): View
    {
        $this->authorize('view', $plan);

        return view('meeting-pack.management.show', compact('plan'));
    }

    public function edit(MeetingPack $plan): View
    {
        $this->authorize('update', $plan);

        return view('meeting-pack.management.edit', compact('plan'));
    }

    public function update(StoreRequest $request, MeetingPack $plan): RedirectResponse
    {
        $this->authorize('update', $plan);
        $validated = $request->validated();

        $validated['updated_by_user_id'] = auth()->id();

        $plan->update($validated);

        return redirect()->route('admin.meeting-packs.show', compact('plan'))
            ->with('success', '面談パックを更新しました。');
    }

    public function destroy(MeetingPack $plan): RedirectResponse
    {
        $this->authorize('delete', $plan);
        $plan->delete();

        return redirect()->route('admin.meeting-packs.index')
            ->with('success', '面談パックを削除しました。');
    }

    public function publish(MeetingPack $plan): RedirectResponse
    {
        $this->authorize('publish', $plan);
        $plan->update(['status' => MeetingPackStatus::Published]);

        return redirect()->route('admin.meeting-packs.show', compact('plan'))
            ->with('success', '面談パックを公開しました。');
    }

    public function archive(MeetingPack $plan): RedirectResponse
    {
        $this->authorize('archive', $plan);
        $plan->update(['status' => MeetingPackStatus::Archived]);

        return redirect()->route('admin.meeting-packs.show', compact('plan'))
            ->with('success', '面談パックをアーカイブしました。');
    }

    public function unarchive(MeetingPack $plan): RedirectResponse
    {
        $this->authorize('unarchive', $plan);
        $plan->update(['status' => MeetingPackStatus::Draft]);

        return redirect()->route('admin.meeting-packs.show', compact('plan'))
            ->with('success', '面談パックを下書きに戻しました。');
    }
}
