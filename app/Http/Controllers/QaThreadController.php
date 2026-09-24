<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\CertificationStatus;
use App\Enums\QaThreadStatus;
use App\Http\Requests\QaThread\IndexRequest;
use App\Http\Requests\QaThread\StoreRequest;
use App\Http\Requests\QaThread\UpdateRequest;
use App\Models\Certification;
use App\Models\QaThread;
use Illuminate\View\View;

class QaThreadController extends Controller
{
    public function index(IndexRequest $request)
    {
        $filters = $request->validated();

        $query = QaThread::query()
            ->with(['user', 'certification'])->withCount('replies')->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['certification_id'])) {
            $query->where('certification_id', $filters['certification_id']);
        }

        if (! empty($filters['keyword'])) {
            $keyword = trim((string) $filters['keyword']);
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('body', 'like', "%{$keyword}%");
            });
        }

        $threads = $query->paginate(10)->appends($filters);
        $certifications = Certification::published()->get();
        $publishedStatus = CertificationStatus::Published;

        return view('qa-thread.index', compact('threads', 'filters', 'certifications', 'publishedStatus'));
    }

    public function create(): View
    {
        $this->authorize('create', QaThread::class);

        $certifications = Certification::published()->get();

        return view('qa-thread.create', compact('certifications'));
    }

    public function store(StoreRequest $request)
    {
        $this->authorize('create', QaThread::class);
        $validatedData = $request->validated();
        $validatedData['user_id'] = auth()->id();
        $validatedData['status'] = QaThreadStatus::Unresolved->value;

        $thread = QaThread::create($validatedData);

        return redirect()->route('qa-board.show', $thread)
            ->with('success', '質問スレッドを作成しました。');
    }

    public function show(QaThread $thread)
    {
        $thread->load(['user', 'certification', 'replies.user']);

        $replies = $thread->replies;

        return view('qa-thread.show', compact('thread', 'replies'));
    }

    public function edit(QaThread $thread)
    {
        $this->authorize('update', $thread);

        $thread->load(['user', 'certification']);

        return view('qa-thread.edit', compact('thread'));
    }

    public function update(UpdateRequest $request, QaThread $thread)
    {
        $this->authorize('update', $thread);

        $validatedData = $request->validated();

        $thread->update($validatedData);

        return redirect()->route('qa-board.show', $thread)
            ->with('success', '質問スレッドを更新しました。');
    }

    public function destroy(QaThread $thread)
    {
        $this->authorize('delete', $thread);

        $thread->delete();

        $redirectRoute = request()->routeIs('admin.*') ? 'admin.qa-board.index' : 'qa-board.index';

        return redirect()->route($redirectRoute)
            ->with('success', '質問スレッドを削除しました。');
    }

    public function resolve(QaThread $thread)
    {
        $this->authorize('resolve', $thread);

        $thread->update([
            'status' => QaThreadStatus::Resolved,
            'resolved_at' => now(),
        ]);

        return redirect()->route('qa-board.show', $thread)
            ->with('success', '質問を解決済みにしました。');
    }

    public function unresolve(QaThread $thread)
    {
        $this->authorize('unresolve', $thread);

        $thread->update([
            'status' => QaThreadStatus::Unresolved,
            'resolved_at' => null,
        ]);

        return redirect()->route('qa-board.show', $thread)
            ->with('success', '質問を未解決に戻しました。');
    }
}
