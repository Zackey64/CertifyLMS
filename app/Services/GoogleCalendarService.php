<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GoogleCredential;
use App\Models\Meeting;
use Carbon\Carbon;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\FreeBusyRequest;
use Google\Service\Oauth2;

class GoogleCalendarService
{
    /**
     * Google カレンダーの指定期間における予定あり時間帯を取得する。
     *
     * @return array<int, array{start: Carbon, end: Carbon}>
     */
    public function getBusyIntervals(
        GoogleCredential $credential,
        Carbon $start,
        Carbon $end,
    ): array {
        $client = $this->createAuthenticatedClient($credential);
        $calendar = new Calendar($client);

        $calendarId = $credential->calendar_id ?: 'primary';

        $request = new FreeBusyRequest([
            'timeMin' => $start->toRfc3339String(),
            'timeMax' => $end->toRfc3339String(),
            'timeZone' => config('app.timezone'),
            'items' => [
                ['id' => $calendarId],
            ],
        ]);

        $response = $calendar->freebusy->query($request);

        $calendarData = $response->getCalendars()[$calendarId] ?? null;

        if ($calendarData === null) {
            throw new \RuntimeException(
                'Google カレンダーの予定情報を取得できませんでした。',
            );
        }

        return collect($calendarData->getBusy() ?? [])
            ->map(fn ($interval) => [
                'start' => Carbon::parse($interval->getStart()),
                'end' => Carbon::parse($interval->getEnd()),
            ])
            ->all();
    }

    /**
     * Google カレンダーに面談予定を登録する。
     */
    public function createMeetingEvent(
        GoogleCredential $credential,
        Meeting $meeting,
    ): string {
        $client = $this->createAuthenticatedClient($credential);
        $calendar = new Calendar($client);

        $startAt = $meeting->scheduled_at;
        $endAt = $startAt->copy()->addMinutes(60);

        $event = new Event([
            'summary' => $meeting->topic ?: '面談',
            'description' => '面談の予約',
            'start' => [
                'dateTime' => $startAt->toIso8601String(),
                'timeZone' => config('app.timezone'),
            ],
            'end' => [
                'dateTime' => $endAt->toIso8601String(),
                'timeZone' => config('app.timezone'),
            ],
        ]);

        $createdEvent = $calendar->events->insert(
            $credential->calendar_id ?: 'primary',
            $event,
        );

        return $createdEvent->getId();
    }

    /**
     * Google カレンダーの面談予定を削除する。
     */
    public function deleteMeetingEvent(
        GoogleCredential $credential,
        string $eventId,
    ): void {
        $client = $this->createAuthenticatedClient($credential);
        $calendar = new Calendar($client);

        $calendar->events->delete(
            $credential->calendar_id ?: 'primary',
            $eventId,
        );
    }

    /**
     * Google の認可 URL を生成する。
     */
    public function createAuthUrl(string $state): string
    {
        $client = $this->createClient();

        $client->setScopes([
            'https://www.googleapis.com/auth/calendar',
            'openid',
            'email',
        ]);

        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setState($state);

        return $client->createAuthUrl();
    }

    /**
     * 認可コードをアクセストークンに交換する。
     */
    public function fetchAccessTokenWithAuthCode(string $code): array
    {
        $client = $this->createClient();

        return $client->fetchAccessTokenWithAuthCode($code);
    }

    /**
     * アクセストークンから Google ユーザー ID を取得する。
     */
    public function getGoogleUserId(array $token): ?string
    {
        $client = $this->createClient();
        $client->setAccessToken($token);

        $oauth = new Oauth2($client);
        $googleUser = $oauth->userinfo->get();

        return $googleUser->id ?: null;
    }

    /**
     * Google API クライアントを生成する。
     */
    protected function createClient(): Client
    {
        $client = new Client;

        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));

        return $client;
    }

    /**
     * 認証済みの Google API クライアントを生成する。
     */
    private function createAuthenticatedClient(
        GoogleCredential $credential,
    ): Client {
        $client = $this->createClient();

        $client->setAccessToken(
            json_decode(
                $credential->access_token,
                true,
                512,
                JSON_THROW_ON_ERROR,
            ),
        );

        if (! $client->isAccessTokenExpired()) {
            return $client;
        }

        $refreshToken = $credential->refresh_token;

        if (! $refreshToken) {
            throw new \RuntimeException(
                'Google カレンダーの再認証が必要です。',
            );
        }

        try {
            $token = $client->fetchAccessTokenWithRefreshToken($refreshToken);
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                'Google カレンダーのアクセストークンを更新できませんでした。',
                previous: $e,
            );
        }

        if (isset($token['error']) || empty($token['access_token'])) {
            throw new \RuntimeException(
                'Google カレンダーのアクセストークンを更新できませんでした。',
            );
        }

        $client->setAccessToken($token);

        $credential->update([
            'access_token' => json_encode($token, JSON_THROW_ON_ERROR),
            'refresh_token' => $token['refresh_token'] ?? $refreshToken,
            'token_expires_at' => now()->addSeconds(
                (int) ($token['expires_in'] ?? 3600),
            ),
        ]);

        return $client;
    }
}
