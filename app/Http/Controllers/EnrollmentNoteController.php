<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\EnrollmentNote\StoreRequest;
use App\Http\Requests\EnrollmentNote\UpdateRequest;
use App\Models\Enrollment;
use App\Models\EnrollmentNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EnrollmentNoteController extends Controller
{
    public function store(StoreRequest $request, Enrollment $enrollment): RedirectResponse
    {
        $this->authorize('create', [EnrollmentNote::class, $enrollment]);
        $validated = $request->validated();

        $enrollment->notes()->create([
            'user_id' => auth()->id(),
            'body' => $validated['body'],
        ]);

        return redirect()->route('enrollments.show', $enrollment)
            ->with('status', 'メモを作成しました。');
    }

    public function edit(EnrollmentNote $note): View
    {
        $this->authorize('update', $note);

        return view('enrollment-note.edit', compact('note'));
    }

    public function update(UpdateRequest $request, EnrollmentNote $note): RedirectResponse
    {
        $this->authorize('update', $note);

        $validated = $request->validated();

        $note->update($validated);

        return redirect()->route('enrollments.show', $note->enrollment)
            ->with('status', 'メモを更新しました。');
    }

    public function destroy(EnrollmentNote $note): RedirectResponse
    {
        $this->authorize('delete', $note);
        $enrollment = $note->enrollment;

        $note->delete();

        return redirect()->route('enrollments.show', $enrollment)
            ->with('status', 'メモを削除しました。');
    }
}
