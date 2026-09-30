<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Password\UpdateRequest;
use Illuminate\Http\RedirectResponse;

class PasswordController extends Controller
{
    public function update(UpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = auth()->user();

        $user->update([
            'password' => $validated['password'],
        ]);

        return redirect()->route('settings.profile.edit')
            ->with('success', 'パスワードを更新しました。');
    }
}
