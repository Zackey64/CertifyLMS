<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\PlanStatus;
use App\Http\Requests\Plan\IndexRequest;
use App\Http\Requests\Plan\StoreRequest;
use App\Http\Requests\Plan\UpdateRequest;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(IndexRequest $request): View
    {
        $this->authorize('viewAny', Plan::class);
        $filters = $request->validated();

        $query = Plan::query()->withCount('users')->ordered();

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

        return view('plan.management.index', compact('plans', 'keyword', 'status'));
    }

    public function create(): View
    {
        $this->authorize('create', Plan::class);

        return view('plan.management.create');
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Plan::class);
        $validated = $request->validated();

        $validated['status'] = PlanStatus::Draft;
        $validated['created_by_user_id'] = auth()->id();
        $validated['updated_by_user_id'] = auth()->id();

        $plan = Plan::create($validated);

        return redirect()->route('admin.plans.show', compact('plan'))
            ->with('success', 'プランを作成しました。');
    }

    public function show(Plan $plan): View
    {
        $this->authorize('view', $plan);

        return view('plan.management.show', compact('plan'));
    }

    public function edit(Plan $plan): View
    {
        $this->authorize('update', $plan);

        return view('plan.management.edit', compact('plan'));
    }

    public function update(UpdateRequest $request, Plan $plan): RedirectResponse
    {
        $this->authorize('update', $plan);
        $validated = $request->validated();

        $validated['updated_by_user_id'] = auth()->id();

        $plan->update($validated);

        return redirect()->route('admin.plans.show', compact('plan'))
            ->with('success', 'プランを更新しました。');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        $this->authorize('delete', $plan);
        $plan->delete();

        return redirect()->route('admin.plans.index')
            ->with('success', 'プランを削除しました。');
    }

    public function publish(Plan $plan): RedirectResponse
    {
        $this->authorize('publish', $plan);
        $plan->update(['status' => PlanStatus::Published]);

        return redirect()->route('admin.plans.show', compact('plan'))
            ->with('success', 'プランを公開しました。');
    }

    public function archive(Plan $plan): RedirectResponse
    {
        $this->authorize('archive', $plan);
        $plan->update(['status' => PlanStatus::Archived]);

        return redirect()->route('admin.plans.show', compact('plan'))
            ->with('success', 'プランをアーカイブしました。');
    }

    public function unarchive(Plan $plan): RedirectResponse
    {
        $this->authorize('unarchive', $plan);
        $plan->update(['status' => PlanStatus::Draft]);

        return redirect()->route('admin.plans.show', compact('plan'))
            ->with('success', 'プランを下書きに戻しました。');
    }
}
