<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::all();
        return response()->json($plans);
    }

    /**
     * Get the user's current subscription.
     */
    public function currentSubscription()
    {

        $user = Auth::user();

        $subscription = Subscription::where('user_id', $user->id)
            ->where('expires_at', '>', Carbon::now()) // Check if subscription is active
            ->with('plan') // Ensure we get plan details
            ->latest('subscribed_at')
            ->first();

        if (!$subscription) {
            return response()->json([
                'subscription' => null,
                'message' => 'No active subscription found.'
            ]);
        }

        return response()->json([
            'subscription' => $subscription
        ]);
    }
}
