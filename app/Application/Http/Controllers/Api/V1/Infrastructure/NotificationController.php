<?php

namespace App\Application\Http\Controllers\Api\V1\Infrastructure;

use App\Application\Http\Controllers\Controller;
use App\Domain\Infrastructure\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::query()
            ->forUser($request->user()->id)
            ->when($request->boolean('unread_only'), fn ($q) => $q->unread())
            ->when($request->input('type'), fn ($q, $type) => $q->ofType($type))
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        return response()->json($notifications);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = Notification::forUser($request->user()->id)
            ->unread()
            ->count();

        return response()->json(['count' => $count]);
    }

    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $notification->markAsRead();

        return response()->json(['data' => $notification]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        Notification::forUser($request->user()->id)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Все уведомления отмечены как прочитанные']);
    }

    public function destroy(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $notification->delete();

        return response()->json(null, 204);
    }

    public function destroyAll(Request $request): JsonResponse
    {
        Notification::forUser($request->user()->id)->delete();

        return response()->json(['message' => 'Все уведомления удалены']);
    }
}
