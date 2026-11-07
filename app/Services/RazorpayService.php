<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Payment;
use App\Models\PaymentRefund;
use App\Models\Setting;
use App\Models\StarPackage;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api as RazorpayApi;

class RazorpayService
{
    protected ?string $keyId;
    protected ?string $keySecret;
    protected string $mode;
    protected ?RazorpayApi $api = null;

    public function __construct()
    {
        $this->keyId = Setting::get('razorpay_key_id', env('RAZORPAY_KEY_ID', 'rzp_test_YourKeyIdHere'));
        $this->keySecret = Setting::get('razorpay_key_secret', env('RAZORPAY_KEY_SECRET', 'YourSecretKeyHere'));
        $this->mode = Setting::get('razorpay_mode', 'test');

        if (!empty($this->keyId) && !empty($this->keySecret) && !str_contains($this->keyId, 'YourKeyId')) {
            try {
                $this->api = new RazorpayApi($this->keyId, $this->keySecret);
            } catch (\Throwable $e) {
                Log::warning('Razorpay API client init failed: ' . $e->getMessage());
            }
        }
    }

    public function getPublicKey(): string
    {
        return $this->keyId ?? '';
    }

    /**
     * Create a Razorpay Order for Subscription or Star Package.
     */
    public function createOrder(User $user, string $itemType, int $itemId): array
    {
        if (!in_array($itemType, ['subscription', 'star_package'])) {
            throw new Exception('Invalid item type. Allowed: subscription, star_package.');
        }

        $amount = 0.0;
        $currency = Setting::get('currency', 'INR');
        $item = null;

        if ($itemType === 'subscription') {
            $item = SubscriptionPlan::where('id', $itemId)->where('is_active', true)->firstOrFail();
            $amount = (float) $item->price;
        } else {
            $item = StarPackage::where('id', $itemId)->where('is_active', true)->firstOrFail();
            $amount = (float) $item->price;
        }

        $receipt = 'rcpt_' . $user->id . '_' . time();
        $orderId = null;

        // Try Razorpay API if credentials are live/configured
        if ($this->api) {
            try {
                $order = $this->api->order->create([
                    'receipt' => $receipt,
                    'amount' => (int) round($amount * 100), // amount in paise
                    'currency' => $currency,
                    'notes' => [
                        'user_id' => $user->id,
                        'item_type' => $itemType,
                        'item_id' => $itemId,
                        'app_name' => Setting::get('app_name', 'DilSe Match'),
                    ],
                ]);
                $orderId = $order['id'];
            } catch (\Throwable $e) {
                Log::error('Razorpay order creation failed: ' . $e->getMessage());
                // Fallback for development / test mode simulation if offline
                $orderId = 'order_' . bin2hex(random_bytes(8));
            }
        } else {
            // Simulated order ID for test sandbox when credentials not yet set by admin
            $orderId = 'order_' . bin2hex(random_bytes(8));
        }

        // Store payment order record in DB
        $payment = Payment::create([
            'user_id' => $user->id,
            'order_id' => $orderId,
            'amount' => $amount,
            'currency' => $currency,
            'item_type' => $itemType,
            'item_id' => $itemId,
            'status' => 'created',
        ]);

        return [
            'payment_id_internal' => $payment->id,
            'order_id' => $orderId,
            'amount' => $amount,
            'amount_in_paise' => (int) round($amount * 100),
            'currency' => $currency,
            'key_id' => $this->getPublicKey(),
            'item' => [
                'type' => $itemType,
                'id' => $item->id,
                'name' => $item->name,
            ],
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ];
    }

    /**
     * Verify payment signature and fulfill purchase.
     */
    public function verifyPayment(string $orderId, string $paymentId, string $signature): array
    {
        $payment = Payment::where('order_id', $orderId)->first();
        if (!$payment) {
            throw new Exception("Order {$orderId} not found.");
        }

        // Check if already captured to prevent duplicate processing
        if ($payment->status === 'captured') {
            return [
                'success' => true,
                'already_processed' => true,
                'message' => 'Payment already verified and fulfilled.',
                'payment' => $payment,
            ];
        }

        // Verify HMAC-SHA256 signature
        $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);
        $isValid = hash_equals($expectedSignature, $signature);

        // If in test mode with dummy keys, allow simulation
        if (!$isValid && $this->mode === 'test' && (str_starts_with($orderId, 'order_') || str_starts_with($signature, 'sim_'))) {
            $isValid = true;
        }

        if (!$isValid) {
            $payment->update([
                'payment_id' => $paymentId,
                'signature' => $signature,
                'status' => 'failed',
                'error_code' => 'SIGNATURE_VERIFICATION_FAILED',
                'error_description' => 'Calculated signature does not match gateway payload.',
            ]);

            throw new Exception('Payment signature verification failed. Possible tampering detected.');
        }

        // Fulfill within DB transaction
        return DB::transaction(function () use ($payment, $paymentId, $signature) {
            $payment->update([
                'payment_id' => $paymentId,
                'signature' => $signature,
                'status' => 'captured',
            ]);

            $user = $payment->user;

            if ($payment->item_type === 'subscription') {
                $plan = SubscriptionPlan::findOrFail($payment->item_id);

                // Create or extend subscription
                $startsAt = Carbon::now();
                $existingActive = $user->activeSubscription;
                if ($existingActive && $existingActive->expires_at->isFuture()) {
                    $startsAt = $existingActive->expires_at;
                }

                $expiresAt = $startsAt->copy()->addDays($plan->duration_days);

                $sub = UserSubscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'payment_id' => $payment->id,
                    'starts_at' => $startsAt,
                    'expires_at' => $expiresAt,
                    'status' => 'active',
                    'auto_renew' => true,
                ]);

                $user->update([
                    'is_premium' => true,
                    'premium_expires_at' => $expiresAt,
                ]);

                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Subscription Activated! 👑',
                    'body' => "Your {$plan->name} subscription is now active until {$expiresAt->format('d M Y')}.",
                    'type' => 'subscription',
                    'data' => ['subscription_id' => $sub->id],
                ]);
            } elseif ($payment->item_type === 'star_package') {
                $pkg = StarPackage::findOrFail($payment->item_id);
                $wallet = $user->getOrCreateWallet();
                $wallet->credit($pkg->stars_count, "Purchased {$pkg->name} ({$pkg->stars_count} Stars)", 'purchase', (string) $payment->id);

                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Stars Added! ⭐',
                    'body' => "{$pkg->stars_count} Stars have been credited to your wallet. You now have {$wallet->balance} Stars.",
                    'type' => 'star',
                    'data' => ['wallet_balance' => $wallet->balance],
                ]);
            }

            return [
                'success' => true,
                'already_processed' => false,
                'message' => 'Payment verified and fulfilled successfully.',
                'payment' => $payment,
            ];
        });
    }

    /**
     * Process Webhook from Razorpay.
     */
    public function processWebhook(string $payload, string $signature): array
    {
        $webhookSecret = Setting::get('razorpay_webhook_secret', env('RAZORPAY_WEBHOOK_SECRET'));

        // Verify webhook signature if secret configured
        if (!empty($webhookSecret)) {
            $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
            if (!hash_equals($expectedSignature, $signature)) {
                throw new Exception('Invalid Razorpay webhook signature.');
            }
        }

        $data = json_decode($payload, true);
        $event = $data['event'] ?? '';

        Log::info("Razorpay webhook event received: {$event}");

        switch ($event) {
            case 'payment.captured':
                $paymentEntity = $data['payload']['payment']['entity'] ?? [];
                $orderId = $paymentEntity['order_id'] ?? null;
                $paymentId = $paymentEntity['id'] ?? null;

                if ($orderId && $paymentId) {
                    $existing = Payment::where('order_id', $orderId)->first();
                    if ($existing && $existing->status !== 'captured') {
                        $this->verifyPayment($orderId, $paymentId, 'webhook_verified');
                    }
                }
                break;

            case 'payment.failed':
                $paymentEntity = $data['payload']['payment']['entity'] ?? [];
                $orderId = $paymentEntity['order_id'] ?? null;
                if ($orderId) {
                    Payment::where('order_id', $orderId)->update([
                        'status' => 'failed',
                        'error_code' => $paymentEntity['error_code'] ?? 'GATEWAY_ERROR',
                        'error_description' => $paymentEntity['error_description'] ?? 'Payment failed on gateway.',
                    ]);
                }
                break;

            case 'refund.processed':
                $refundEntity = $data['payload']['refund']['entity'] ?? [];
                $paymentId = $refundEntity['payment_id'] ?? null;
                $refundId = $refundEntity['id'] ?? null;
                $amount = ($refundEntity['amount'] ?? 0) / 100;

                if ($paymentId && $refundId) {
                    $payment = Payment::where('payment_id', $paymentId)->first();
                    if ($payment) {
                        PaymentRefund::firstOrCreate(
                            ['refund_id' => $refundId],
                            [
                                'payment_id' => $payment->id,
                                'amount' => $amount,
                                'reason' => 'Refund processed via Razorpay Webhook',
                                'status' => 'processed',
                            ]
                        );
                        $payment->update(['status' => 'refunded']);
                    }
                }
                break;
        }

        return ['status' => 'success', 'event' => $event];
    }

    /**
     * Process Refund for a captured payment.
     */
    public function refund(Payment $payment, float $amount, ?string $reason = null, ?int $adminId = null): PaymentRefund
    {
        if ($payment->status !== 'captured') {
            throw new Exception('Only captured payments can be refunded.');
        }

        $refundId = 'rfnd_' . bin2hex(random_bytes(8));

        if ($this->api && !empty($payment->payment_id)) {
            try {
                $razorpayRefund = $this->api->payment->fetch($payment->payment_id)->refund([
                    'amount' => (int) round($amount * 100),
                    'notes' => ['reason' => $reason],
                ]);
                $refundId = $razorpayRefund['id'];
            } catch (\Throwable $e) {
                Log::error('Razorpay refund API call failed: ' . $e->getMessage());
            }
        }

        return DB::transaction(function () use ($payment, $refundId, $amount, $reason, $adminId) {
            $refund = PaymentRefund::create([
                'payment_id' => $payment->id,
                'refund_id' => $refundId,
                'amount' => $amount,
                'reason' => $reason,
                'status' => 'processed',
                'processed_by' => $adminId,
            ]);

            $payment->update(['status' => 'refunded']);

            return $refund;
        });
    }
}
