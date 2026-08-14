<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function assign(Request $request, User $user)
    {
        $data = $request->validate([
            'package_id'    => 'required|exists:packages,id',
            'billing_cycle' => 'required|in:monthly,yearly,lifetime',
            'starts_at'     => 'required|date',
            'expires_at'    => 'nullable|date|after:starts_at',
            'note'          => 'nullable|string|max:300',
        ]);

        $data['user_id']     = $user->id;
        $data['assigned_by'] = auth()->id();

        UserSubscription::create($data);

        // reactivate / deactivate children based on new package limit
        $user->syncChildActivation();

        return response()->json(['ok' => true]);
    }

    public function history(User $user)
    {
        $subs = $user->subscriptions()->with('package', 'assignedBy')->latest()->get();
        return response()->json($subs);
    }
}
