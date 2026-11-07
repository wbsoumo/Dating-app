<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\StarPackage;
use App\Models\SubscriptionPlan;
use App\Models\Swipe;
use App\Models\User;
use App\Models\UserMatch;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoActivitySeeder extends Seeder
{
    public function run(): void
    {
        $users = User::limit(50)->get();
        if ($users->count() < 10) {
            return;
        }

        $now = Carbon::now();
        $plans = SubscriptionPlan::all();
        $starPackages = StarPackage::all();

        // Create some sample swipes and matches
        for ($i = 0; $i < 20; $i += 2) {
            $u1 = $users[$i] ?? null;
            $u2 = $users[$i + 1] ?? null;

            if (!$u1 || !$u2) {
                break;
            }

            // Both swipe like
            Swipe::firstOrCreate(
                ['user_id' => $u1->id, 'target_user_id' => $u2->id],
                ['type' => 'like', 'created_at' => $now->copy()->subHours(random_int(1, 48))]
            );

            Swipe::firstOrCreate(
                ['user_id' => $u2->id, 'target_user_id' => $u1->id],
                ['type' => 'like', 'created_at' => $now->copy()->subHours(random_int(1, 48))]
            );

            // Mutual match
            $user1Id = min($u1->id, $u2->id);
            $user2Id = max($u1->id, $u2->id);

            UserMatch::firstOrCreate(
                ['user1_id' => $user1Id, 'user2_id' => $user2Id],
                [
                    'status' => 'active',
                    'matched_at' => $now->copy()->subHours(random_int(1, 36)),
                    'last_message_at' => $now->copy()->subMinutes(random_int(10, 600)),
                ]
            );
        }

        // Create some sample payments & subscriptions
        for ($i = 0; $i < 15; $i++) {
            $user = $users[$i] ?? null;
            if (!$user) {
                continue;
            }

            $isSub = ($i % 2 === 0);
            $plan = $plans->random();
            $starPkg = $starPackages->random();

            $orderId = 'order_demo_' . Str::random(10);
            $paymentId = 'pay_demo_' . Str::random(10);
            $amount = $isSub ? $plan->price : $starPkg->price;
            $itemType = $isSub ? 'subscription' : 'star_package';
            $itemId = $isSub ? $plan->id : $starPkg->id;

            $payment = Payment::create([
                'user_id' => $user->id,
                'order_id' => $orderId,
                'payment_id' => $paymentId,
                'signature' => hash_hmac('sha256', $orderId . '|' . $paymentId, 'demo_secret'),
                'amount' => $amount,
                'currency' => 'INR',
                'item_type' => $itemType,
                'item_id' => $itemId,
                'status' => 'captured',
                'method' => ($i % 3 === 0) ? 'upi' : (($i % 3 === 1) ? 'card' : 'netbanking'),
                'razorpay_response' => ['id' => $paymentId, 'entity' => 'payment', 'status' => 'captured'],
                'created_at' => $now->copy()->subDays(random_int(0, 14))->subHours(random_int(1, 23)),
            ]);

            if ($isSub) {
                UserSubscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'payment_id' => $payment->id,
                    'starts_at' => $now->copy()->subDays(random_int(1, 5)),
                    'expires_at' => $now->copy()->addDays($plan->duration_days),
                    'status' => 'active',
                    'auto_renew' => true,
                ]);

                $user->update([
                    'is_premium' => true,
                    'premium_expires_at' => $now->copy()->addDays($plan->duration_days),
                ]);
            } else {
                $wallet = $user->getOrCreateWallet();
                $wallet->credit($starPkg->stars_count, "Purchased {$starPkg->name}", 'purchase', (string) $payment->id);
            }
        }
    }
}
