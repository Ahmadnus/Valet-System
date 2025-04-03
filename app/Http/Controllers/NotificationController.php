<?php

namespace App\Http\Controllers;



use App\Models\User;
use Illuminate\Http\Request;
use App\Services\FirebaseNotificationService;
use App\services\NotificationService;
use App\NotificationType;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Models\Notification as ModelNotification;
use Kreait\Laravel\Firebase\Facades\Firebase;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $notificationService) {}

    public function notifyUser(Request $request)
    {
        $user = User::find($request->user_id);

        $type = NotificationType::from($request->type);

        $response = $this->notificationService->sendNotification(
            $request->title,
            $request->body,

            $request->data ?? [],
            topic: 'user_topic',
            type: $type
        );

        return response()->json([
            'success' => $response !== null,
            'message' => $response ? $type->successMessage() : $type->failureMessage(),
        ]);
    }

    public function notifyTopic(Request $request)
    {
        $type = NotificationType::from($request->type);

        $response = $this->notificationService->sendNotification(
            $request->title,
            $request->body,
            null,
            $request->data ?? [],
            topic: $request->topic ?? 'all',
            type: $type
        );

        return response()->json([
            'success' => $response !== null,
            'message' => $response ? $type->successMessage() : $type->failureMessage(),
        ]);
    }
    // app/Http/Controllers/NotifyController.php

public function notifyManyUsers(Request $request)
{
    $type = NotificationType::from($request->type);

    $users = User::whereIn('id', $request->user_ids ?? [])->get();

    $success = $this->notificationService->sendNotificationToMany(
        $users,
        $request->title,
        $request->body,
        $request->data ?? [],
        topic: $request->topic ?? 'all',
        type: $type
    );

    return response()->json([
        'success' => $success !== null,
        'message' => $success ? $type->successMessage() : $type->failureMessage(),
    ]);
}
}