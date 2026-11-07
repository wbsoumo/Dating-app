<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatingAppApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_user_can_register_via_api(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Kavya Sen',
            'email' => 'kavya@test.com',
            'phone' => '919876500001',
            'password' => 'Pass@12345',
            'gender' => 'female',
            'dob' => '1999-04-12',
            'latitude' => 28.6139,
            'longitude' => 77.2090,
            'city' => 'New Delhi',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'message' => 'User registered successfully.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'token_type',
                    'user' => ['id', 'name', 'email', 'gender', 'age'],
                ]
            ]);

        $this->assertDatabaseHas('users', ['email' => 'kavya@test.com']);
        $this->assertDatabaseHas('profiles', ['user_id' => $response->json('data.user.id')]);
        $this->assertDatabaseHas('user_locations', ['user_id' => $response->json('data.user.id'), 'city' => 'New Delhi']);
        $this->assertDatabaseHas('star_wallets', ['user_id' => $response->json('data.user.id')]);
    }

    public function test_user_can_login_via_api(): void
    {
        // First register
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Arjun Roy',
            'email' => 'arjun@test.com',
            'password' => 'Pass@12345',
            'gender' => 'male',
            'dob' => '1997-08-20',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'login' => 'arjun@test.com',
            'password' => 'Pass@12345',
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => true])
            ->assertJsonStructure(['data' => ['token', 'user']]);
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        $registerRes = $this->postJson('/api/v1/auth/register', [
            'name' => 'Sneha Patel',
            'email' => 'sneha@test.com',
            'password' => 'Pass@12345',
            'gender' => 'female',
            'dob' => '1996-02-14',
        ]);

        $token = $registerRes->json('data.token');

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/user/profile');

        $response->assertStatus(200)
            ->assertJson(['status' => true])
            ->assertJsonPath('data.name', 'Sneha Patel')
            ->assertJsonStructure([
                'data' => [
                    'id', 'name', 'email', 'gender', 'age',
                    'wallet' => ['stars_balance'],
                    'swipe_limits' => ['daily_limit', 'used_today', 'remaining_today', 'can_swipe'],
                ]
            ]);
    }

    public function test_swipes_and_mutual_match_detection(): void
    {
        // User 1
        $u1Res = $this->postJson('/api/v1/auth/register', [
            'name' => 'User One',
            'email' => 'user1@test.com',
            'password' => 'Pass@12345',
            'gender' => 'male',
            'dob' => '1998-01-01',
        ]);
        $token1 = $u1Res->json('data.token');
        $u1Id = $u1Res->json('data.user.id');

        // User 2
        $u2Res = $this->postJson('/api/v1/auth/register', [
            'name' => 'User Two',
            'email' => 'user2@test.com',
            'password' => 'Pass@12345',
            'gender' => 'female',
            'dob' => '1999-01-01',
        ]);
        $token2 = $u2Res->json('data.token');
        $u2Id = $u2Res->json('data.user.id');

        // User 1 swipes like on User 2 (No match yet)
        $swipe1 = $this->withHeader('Authorization', "Bearer {$token1}")
            ->postJson('/api/v1/swipes', [
                'target_user_id' => $u2Id,
                'type' => 'like',
            ]);

        $swipe1->assertStatus(200)
            ->assertJsonPath('data.is_match', false);

        // User 2 reciprocal swipes like on User 1 (Mutual Match detected!)
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $swipe2 = $this->withHeader('Authorization', "Bearer {$token2}")
            ->postJson('/api/v1/swipes', [
                'target_user_id' => $u1Id,
                'type' => 'like',
            ]);

        $swipe2->assertStatus(200)
            ->assertJsonPath('data.is_match', true)
            ->assertJsonStructure([
                'data' => [
                    'is_match',
                    'match' => ['id', 'matched_at', 'partner'],
                ]
            ]);

        $this->assertDatabaseHas('matches', [
            'user1_id' => min($u1Id, $u2Id),
            'user2_id' => max($u1Id, $u2Id),
            'status' => 'active',
        ]);
    }

    public function test_razorpay_order_creation_and_verification(): void
    {
        $reg = $this->postJson('/api/v1/auth/register', [
            'name' => 'VIP Buyer',
            'email' => 'buyer@test.com',
            'password' => 'Pass@12345',
            'gender' => 'male',
            'dob' => '1995-05-05',
        ]);
        $token = $reg->json('data.token');

        $plan = SubscriptionPlan::first();

        // 1. Create order
        $orderRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/payments/create-order', [
                'item_type' => 'subscription',
                'item_id' => $plan->id,
            ]);

        $orderRes->assertStatus(200)
            ->assertJson(['status' => true])
            ->assertJsonStructure(['data' => ['order_id', 'amount', 'currency', 'key_id']]);

        $orderId = $orderRes->json('data.order_id');
        $paymentId = 'pay_' . bin2hex(random_bytes(6));
        $signature = 'sim_signature_test';

        // 2. Verify payment
        $verifyRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/payments/verify', [
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
            ]);

        $verifyRes->assertStatus(200)
            ->assertJson(['status' => true]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $orderId,
            'status' => 'captured',
        ]);

        $this->assertDatabaseHas('user_subscriptions', [
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);
    }
}
