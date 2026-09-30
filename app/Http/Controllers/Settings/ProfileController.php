<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user();

        return view('settings.profile', ['user' => $user]);
    }

    public function update(UpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = auth()->user();

        $user->update($validated);

        return redirect()->route('settings.profile.edit')
            ->with('success', 'プロフィールを更新しました。');
    }
}
