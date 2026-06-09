<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    private const PRICE        = 19000;
    private const DURATION     = 30;
    private const XENDIT_URL   = 'https://api.xendit.co/v2/invoices';

    /**
     * Show the subscription page.
     */
    public function index()
    {
        $user           = Auth::user();
        $active         = $user->activeSubscription();
        $pendingInvoice = null;

        $pendingSub = $user->subscriptions()
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($pendingSub && $pendingSub->xendit_invoice_id) {
            $response = Http::withBasicAuth(env('XENDIT_SECRET_KEY'), '')
                ->get('https://api.xendit.co/v2/invoices/' . $pendingSub->xendit_invoice_id);

            if ($response->successful()) {
                $data   = $response->json();
                $status = strtolower($data['status'] ?? '');

                if ($status === 'paid' || $status === 'settled') {
                    $paidAt = now();
                    $pendingSub->update([
                        'status'          => 'paid',
                        'starts_at'       => $paidAt,
                        'expires_at'      => $paidAt->copy()->addDays(self::DURATION),
                        'payment_method'  => $data['payment_method'] ?? null,
                        'payment_channel' => $data['payment_channel'] ?? null,
                        'xendit_payload'  => $data,
                    ]);
                    $active = $user->activeSubscription();
                } elseif (in_array($status, ['expired', 'failed'])) {
                    $pendingSub->update(['status' => $status, 'xendit_payload' => $data]);
                } else {
                    $pendingInvoice = $pendingSub;
                }
            } else {
                $pendingInvoice = $pendingSub;
            }
        }

        return view('premium.index', compact('user', 'active', 'pendingInvoice'));
    }

    /**
     * Create a Xendit invoice and redirect the user.
     */
    public function subscribe(Request $request)
    {
        $user = Auth::user();

        if ($user->activeSubscription()) {
            return back()->with('status', 'Kamu sudah berlangganan premium.');
        }

        $externalId = 'sub-' . $user->id . '-' . Str::random(8) . '-' . time();

        $ngrokBase  = env('APP_NGROK_URL', config('app.url'));

        $response = Http::withBasicAuth(env('XENDIT_SECRET_KEY'), '')
            ->post(self::XENDIT_URL, [
                'external_id'         => $externalId,
                'amount'              => self::PRICE,
                'currency'            => 'IDR',
                'description'         => 'Bacanovel Premium – 30 Hari',
                'customer'            => [
                    'given_names' => $user->name,
                    'email'       => $user->email,
                ],
                'customer_notification_preference' => [
                    'invoice_created'  => ['email'],
                    'invoice_reminder' => ['email'],
                    'invoice_paid'     => ['email'],
                ],
                'success_redirect_url' => $ngrokBase . '/subscription/success',
                'failure_redirect_url' => $ngrokBase . '/subscription',
                'items'                => [
                    [
                        'name'     => 'Bacanovel Premium 30 Hari',
                        'quantity' => 1,
                        'price'    => self::PRICE,
                    ],
                ],
            ]);

        if ($response->failed()) {
            Log::error('Xendit invoice creation failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return back()->with('error', 'Gagal membuat invoice pembayaran. Silakan coba lagi.');
        }

        $data = $response->json();

        Subscription::create([
            'user_id'          => $user->id,
            'xendit_invoice_id' => $data['id'],
            'external_id'      => $externalId,
            'status'           => 'pending',
            'amount'           => self::PRICE,
            'duration_days'    => self::DURATION,
            'xendit_payload'   => $data,
        ]);

        return redirect($data['invoice_url']);
    }

    /**
     * Success redirect page after payment.
     */
    public function success()
    {
        return redirect()->route('subscription.index')->with('status', 'Pembayaran selesai. Status langganan akan diperbarui otomatis.');
    }

    /**
     * Xendit webhook callback (POST /xendit/webhook).
     */
    public function webhook(Request $request)
    {
        // Verify Xendit callback token
        $token = $request->header('x-callback-token');
        $expectedToken = env('XENDIT_WEBHOOK_TOKEN');

        if ($expectedToken && $token !== $expectedToken) {
            Log::warning('Xendit webhook token mismatch', ['received' => $token]);
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $payload = $request->json()->all();

        Log::info('Xendit webhook received', $payload);

        $externalId = $payload['external_id'] ?? null;
        $status     = strtolower($payload['status'] ?? '');

        if (! $externalId) {
            return response()->json(['message' => 'Missing external_id'], 400);
        }

        $subscription = Subscription::where('external_id', $externalId)->first();

        if (! $subscription) {
            return response()->json(['message' => 'Subscription not found'], 404);
        }

        if ($status === 'paid' || $status === 'settled') {
            $paidAt = now();
            $subscription->update([
                'status'          => 'paid',
                'starts_at'       => $paidAt,
                'expires_at'      => $paidAt->copy()->addDays(self::DURATION),
                'payment_method'  => $payload['payment_method'] ?? null,
                'payment_channel' => $payload['payment_channel'] ?? null,
                'xendit_payload'  => $payload,
            ]);
        } elseif (in_array($status, ['expired', 'failed'])) {
            $subscription->update([
                'status'         => $status,
                'xendit_payload' => $payload,
            ]);
        }

        return response()->json(['message' => 'OK']);
    }
}
