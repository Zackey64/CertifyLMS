<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $this->authorize('viewAny', DatabaseNotification::class);

        $query = $user->notifications()->latest();
        if ($request->query('tab') === 'unread') {
            $query->whereNull('read_at');
        }
        $notifications = $query->limit(20)->get();

        return NotificationResource::collection($notifications)->additional([
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request, DatabaseNotification $notification): NotificationResource
    {
        $user = $request->user();

        $notification = $user->notifications()
            ->findOrFail($notification->id);

        $this->authorize('markAsRead', $notification);

        $notification->markAsRead();

        return new NotificationResource($notification);
    }

    public function readAll(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->authorize('markAllAsRead', DatabaseNotification::class);

        $user->unreadNotifications()->update([
            'read_at' => now(),
        ]);

        return response()->json([
            'message' => 'すべての通知を既読にしました。',
            'unread_count' => 0,
        ]);
    }
}
