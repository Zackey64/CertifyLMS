<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\QaReply\StoreRequest;
use App\Http\Requests\QaReply\UpdateRequest;
use App\Models\QaReply;
use App\Models\QaThread;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QaReplyController extends Controller
{
    public function store(StoreRequest $request, QaThread $thread): RedirectResponse
    {
        $this->authorize('create', QaReply::class);

        $thread->replies()->create([
            'user_id' => auth()->id(),
            'body' => $request->validated('body'),
        ]);

        return redirect()->route('qa-board.show', ['thread' => $thread])
            ->with('success', '回答を投稿しました。');
    }

    public function edit(QaThread $thread, QaReply $reply): View
    {
        $this->authorize('update', $reply);

        return view('qa-thread.reply-edit', compact('thread', 'reply'));
    }

    public function update(UpdateRequest $request, QaThread $thread, QaReply $reply): RedirectResponse
    {
        $this->authorize('update', $reply);

        $reply->update($request->validated());

        return redirect()->route('qa-board.show', $thread)
            ->with('success', '回答を更新しました。');
    }

    public function destroy(QaThread $thread, QaReply $reply): RedirectResponse
    {
        $this->authorize('delete', $reply);

        $reply->delete();

        $redirectRoute = request()->routeIs('admin.*') ? 'admin.qa-board.show' : 'qa-board.show';

        return redirect()->route($redirectRoute, $thread)
            ->with('success', '回答を削除しました。');
    }
}
