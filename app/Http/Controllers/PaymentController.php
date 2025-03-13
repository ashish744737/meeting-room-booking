<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\SubscriptionPlan;
use App\Models\Subscription;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'transaction_no' => 'required|unique:payments,transaction_no',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric',
            'payment_mode' => 'required|string',
            'net_amount' => 'required|numeric',
        ]);

        $payment = Payment::create([
            'user_id' => Auth::id(),
            'subscription_plan_id' => $request->subscription_plan_id,
            'transaction_no' => $request->transaction_no,
            'payment_date' => Carbon::parse($request->payment_date),
            'amount' => $request->amount,
            'payment_mode' => $request->payment_mode,
            'net_amount' => $request->net_amount,
        ]);

        $this->subscribe($request->subscription_plan_id);

        return response()->json(['message' => 'Payment recorded successfully!', 'payment' => $payment], 201);
    }

    public function getUserPayments()
    {
        $payments = Payment::where('user_id', Auth::id())->with('subscriptionPlan')->latest()->get();
        return response()->json(['payments' => $payments]);
    }

    public function subscribe($subscription_plan_id)
    {
        $user = Auth::user();
        $plan = SubscriptionPlan::findOrFail($subscription_plan_id);

        // Check if the user already has an active subscription
        $existingSubscription = Subscription::where('user_id', $user->id)
            ->where('expires_at', '>', Carbon::now()) // Check if still active
            ->first();

        if ($existingSubscription) {
            return response()->json([
                'message' => 'You already have an active subscription.'
            ], 400);
        }

        // Create a new subscription (valid for 30 days)
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'subscribed_at' => now(),
            'expires_at' => Carbon::now()->addDays(30),
        ]);

        $user->update(['subscription' => $plan->name]);

        return response()->json([
            'message' => 'Subscription successful!',
            'subscription' => $subscription
        ], 201);
    }
}
