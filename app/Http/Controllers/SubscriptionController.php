<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    public function __construct(private readonly SubscriptionService $subscriptionService) {}

    public function index(Request $request)
    {
        $user           = $request->user();
        $pendingInvoice = $this->subscriptionService->syncPendingSubscription($user);
        $active         = $user->fresh()->activeSubscription();

        return view('premium.index', compact('user', 'active', 'pendingInvoice'));
    }

    public function subscribe(Request $request)
    {
        $user = $request->user();

        if ($user->activeSubscription()) {
            return back()->with('status', 'You are already subscribed to premium.');
        }

        try {
            $invoiceUrl = $this->subscriptionService->createInvoice($user);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage() . ' Please try again.');
        }

        return redirect($invoiceUrl);
    }

    public function success()
    {
        return redirect()->route('subscription.index')
            ->with('status', 'Payment successful. Your subscription status will be updated automatically.');
    }

    public function webhook(Request $request)
    {
        $token = $request->header('x-callback-token', '');

        if (! $this->subscriptionService->verifyWebhookToken($token)) {
            Log::warning('Xendit webhook token mismatch', ['received' => $token]);
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $payload    = $request->json()->all();
        $externalId = $payload['external_id'] ?? null;

        Log::info('Xendit webhook received', $payload);

        if (! $externalId) {
            return response()->json(['message' => 'Missing external_id'], 400);
        }

        $found = $this->subscriptionService->handleWebhook($payload);

        if (! $found) {
            return response()->json(['message' => 'Subscription not found'], 404);
        }

        return response()->json(['message' => 'OK']);
    }
}
