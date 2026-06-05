<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PushSubscription;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushController extends Controller
{
    public function subscribe(Request $request)
    {
        $user = Auth::user();

        PushSubscription::updateOrCreate(
            ['endpoint' => $request->endpoint],
            [
                'user_id'    => $user->id,
                'public_key' => $request->keys['p256dh'],
                'auth_token' => $request->keys['auth'],
            ]
        );

        return response()->json(['success' => true]);
    }

    public function unsubscribe(Request $request)
    {
        PushSubscription::where('endpoint', $request->endpoint)->delete();
        return response()->json(['success' => true]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'title'   => 'required|string|max:100',
            'body'    => 'required|string|max:300',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);

        $auth = [
            'VAPID' => [
                'subject'    => config('services.vapid.subject'),
                'publicKey'  => config('services.vapid.public_key'),
                'privateKey' => config('services.vapid.private_key'),
            ],
        ];

        $webPush = new WebPush($auth);

        $payload = json_encode([
            'title' => $request->title,
            'body'  => $request->body,
            'url'   => $request->url ?? '/',
        ]);

        $query = PushSubscription::query();
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        foreach ($query->get() as $sub) {
            $webPush->queueNotification(
                Subscription::create([
                    'endpoint'        => $sub->endpoint,
                    'keys' => [
                        'p256dh' => $sub->public_key,
                        'auth'   => $sub->auth_token,
                    ],
                ]),
                $payload
            );
        }

        $results = [];
        foreach ($webPush->flush() as $report) {
            $results[] = [
                'endpoint' => $report->getRequest()->getUri(),
                'success'  => $report->isSuccess(),
            ];
            if ($report->isSubscriptionExpired()) {
                PushSubscription::where('endpoint', (string) $report->getRequest()->getUri())->delete();
            }
        }

        return response()->json(['success' => true, 'results' => $results]);
    }
}
