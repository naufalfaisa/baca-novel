<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SubscriptionService
{
    private string $secretKey;
    private string $invoiceUrl;
    private int $price;
    private int $durationDays;
    private string $description;
    private string $itemName;

    public function __construct()
    {
        $this->secretKey    = config('xendit.secret_key');
        $this->invoiceUrl   = config('xendit.invoice_url');
        $this->price        = config('xendit.subscription.price');
        $this->durationDays = config('xendit.subscription.duration_days');
        $this->description  = config('xendit.subscription.description');
        $this->itemName     = config('xendit.subscription.item_name');
    }

    /**
     * Sync a pending subscription by checking its status from Xendit.
     * Returns the updated subscription or null if no pending found.
     */
    public function syncPendingSubscription(User $user): ?Subscription
    {
        $pendingSub = $user->subscriptions()
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (! $pendingSub || ! $pendingSub->xendit_invoice_id) {
            return null;
        }

        $response = Http::withBasicAuth($this->secretKey, '')
            ->get("{$this->invoiceUrl}/{$pendingSub->xendit_invoice_id}");

        if (! $response->successful()) {
            return $pendingSub;
        }

        $data   = $response->json();
        $status = strtolower($data['status'] ?? '');

        if ($status === 'paid' || $status === 'settled') {
            $this->markAsPaid($pendingSub, $data);
            return null; // no longer pending
        }

        if (in_array($status, ['expired', 'failed'])) {
            $pendingSub->update(['status' => $status, 'xendit_payload' => $data]);
            return null;
        }

        return $pendingSub; // still pending
    }

    /**
     * Create a Xendit invoice and store a pending Subscription record.
     * Returns the invoice URL on success, or throws on failure.
     */
    public function createInvoice(User $user): string
    {
        $externalId = 'sub-' . $user->id . '-' . Str::random(8) . '-' . time();
        $baseUrl    = config('app.url');

        $response = Http::withBasicAuth($this->secretKey, '')
            ->post($this->invoiceUrl, [
                'external_id'  => $externalId,
                'amount'       => $this->price,
                'currency'     => 'IDR',
                'description'  => $this->description,
                'customer'     => [
                    'given_names' => $user->name,
                    'email'       => $user->email,
                ],
                'customer_notification_preference' => [
                    'invoice_created'  => ['email'],
                    'invoice_reminder' => ['email'],
                    'invoice_paid'     => ['email'],
                ],
                'success_redirect_url' => $baseUrl . '/subscription/success',
                'failure_redirect_url' => $baseUrl . '/subscription',
                'items'                => [
                    [
                        'name'     => $this->itemName,
                        'quantity' => 1,
                        'price'    => $this->price,
                    ],
                ],
            ]);

        if ($response->failed()) {
            Log::error('Xendit invoice creation failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Failed to make invoice payment.');
        }

        $data = $response->json();

        DB::transaction(function () use ($user, $externalId, $data) {
            Subscription::create([
                'user_id'           => $user->id,
                'xendit_invoice_id' => $data['id'],
                'external_id'       => $externalId,
                'status'            => 'pending',
                'amount'            => $this->price,
                'duration_days'     => $this->durationDays,
                'xendit_payload'    => $data,
            ]);
        });

        return $data['invoice_url'];
    }

    /**
     * Handle an incoming Xendit webhook payload.
     * Returns true if the webhook was processed, false if subscription not found.
     */
    public function handleWebhook(array $payload): bool
    {
        $externalId = $payload['external_id'] ?? null;
        $status     = strtolower($payload['status'] ?? '');

        if (! $externalId) {
            return false;
        }

        $subscription = Subscription::where('external_id', $externalId)->first();

        if (! $subscription) {
            return false;
        }

        if ($status === 'paid' || $status === 'settled') {
            $this->markAsPaid($subscription, $payload);
        } elseif (in_array($status, ['expired', 'failed'])) {
            $subscription->update(['status' => $status, 'xendit_payload' => $payload]);
        }

        return true;
    }

    /**
     * Verify the Xendit webhook token from request header.
     */
    public function verifyWebhookToken(string $token): bool
    {
        $expected = config('xendit.webhook_token');

        return ! $expected || $token === $expected;
    }

    /**
     * Mark a subscription as paid with activation timestamps.
     */
    private function markAsPaid(Subscription $subscription, array $data): void
    {
        $paidAt = now();

        $subscription->update([
            'status'          => 'paid',
            'starts_at'       => $paidAt,
            'expires_at'      => $paidAt->copy()->addDays($this->durationDays),
            'payment_method'  => $data['payment_method'] ?? null,
            'payment_channel' => $data['payment_channel'] ?? null,
            'xendit_payload'  => $data,
        ]);
    }
}
