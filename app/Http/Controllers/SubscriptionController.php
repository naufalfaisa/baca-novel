<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    public function __construct(private readonly SubscriptionService $subscriptionService) {}

    /**
     * Display the subscription page.
     *
     * Syncs any pending Xendit invoice before rendering so the status
     * is always current without needing to wait for a webhook.
     */
    public function index(Request $request)
    {
        $user           = $request->user();
        $pendingInvoice = $this->subscriptionService->syncPendingSubscription($user);
        $active         = $user->fresh()->activeSubscription();

        return view('premium.index', compact('user', 'active', 'pendingInvoice'));
    }

    /**
     * Create a Xendit invoice and redirect the user to the payment page.
     */
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

    /**
     * Handle the success redirect from Xendit after payment.
     *
     * The actual status update is handled by the webhook or the next index() poll.
     */
    public function success()
    {
        return redirect()->route('subscription.index')
            ->with('status', 'Payment successful. Your subscription status will be updated automatically.');
    }

    /**
     * Handle an incoming Xendit webhook callback.
     *
     * This route is CSRF-exempt (configured in bootstrap/app.php).
     */
    public function webhook(Request $request)
    {
        $token = $request->header('x-callback-token', '');

        // Verify the request is genuinely from Xendit.
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
