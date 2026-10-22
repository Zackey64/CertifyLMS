<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Google\Client;
use Google\Client as GoogleClient;
use Google\Service\Calendar;
use Google\Service\Oauth2;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GoogleCalendarController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $client = new GoogleClient;
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->setScopes([
            'https://www.googleapis.com/auth/calendar',
            'openid',
            'email',
        ]);

        $client->setAccessType('offline');
        $client->setPrompt('consent');

        $state = bin2hex(random_bytes(32));
        $request->session()->put('google_calendar_oauth_state', $state);

        $client->setState($state);

        return redirect()->away($client->createAuthUrl());
    }

    public function callback(Request $request): RedirectResponse
    {
        $expectedState = $request->session()->pull('google_calendar_oauth_state');
        $actualState = $request->query('state');

        if (! is_string($expectedState) || ! is_string($actualState) || ! hash_equals($expectedState, $actualState)) {
            return redirect()->route('settings.availability.index')
                ->with('error', 'Google カレンダーの認証を確認できませんでした。もう一度お試しください。');
        }

        if ($request->filled('error')) {
            return redirect()->route('settings.availability.index')
                ->with('error', 'Google カレンダーとの連携がキャンセルされました。');
        }

        $code = $request->query('code');

        if (! is_string($code) || $code === '') {
            return redirect()->route('settings.availability.index')
                ->with('error', 'Google から認証コードを取得できませんでした。');
        }

        $client = new Client;
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));

        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error']) || empty($token['access_token'])) {
            return redirect()->route('settings.availability.index')
                ->with('error', 'Google からアクセストークンを取得できませんでした。');
        }

        $client->setAccessToken($token);

        $oauth = new Oauth2($client);
        $googleUser = $oauth->userinfo->get();

        if (! $googleUser->id) {
            return redirect()->route('settings.availability.index')
                ->with('error', 'Google アカウントを確認できませんでした。');
        }

        $calendar = new Calendar($client);

        $request->user()->googleCredential()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'google_user_id' => $googleUser->id,
                'calendar_id' => 'primary',
                'access_token' => json_encode($token, JSON_THROW_ON_ERROR),
                'refresh_token' => $token['refresh_token'] ?? $request->user()->googleCredential?->refresh_token,
                'token_expires_at' => now()->addSeconds((int) ($token['expires_in'] ?? 3600)),
                'connected_at' => now(),
            ],
        );

        return redirect()->route('settings.availability.index')
            ->with('success', 'Google カレンダーと連携しました。');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->user()->googleCredential()->delete();

        return redirect()->route('settings.availability.index')
            ->with('success', 'Google カレンダーの連携を解除しました。');
    }
}
