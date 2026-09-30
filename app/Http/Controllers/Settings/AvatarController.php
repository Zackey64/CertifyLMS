<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Avatar\StoreRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class AvatarController extends Controller
{
    public function store(StoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = auth()->user();

        $path = $validated['avatar']->store('avatars', 'public');

        $user->update([
            'avatar_url' => Storage::url($path),
        ]);

        return redirect()->route('settings.profile.edit')
            ->with('success', 'アイコン画像を更新しました。');
    }

    public function destroy(): RedirectResponse
    {
        $user = auth()->user();

        $avatarUrl = $user->avatar_url;

        if (! $avatarUrl) {
            return redirect()->route('settings.profile.edit')
                ->with('error', 'アイコン画像が存在しません。');
        }

        $path = str_replace('/storage/', '', $avatarUrl);
        Storage::disk('public')->delete($path);

        $user->update([
            'avatar_url' => null,
        ]);

        return redirect()->route('settings.profile.edit')
            ->with('success', 'アイコン画像を削除しました。');
    }
}
