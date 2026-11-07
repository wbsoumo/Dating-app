<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoActivitySeeder;
use Database\Seeders\DemoUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_log_in_and_access_dashboard(): void
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@datingapp.com',
            'password' => 'Admin@123456',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs(Admin::where('email', 'admin@datingapp.com')->first(), 'admin');

        $dash = $this->get('/admin/dashboard');
        $dash->assertStatus(200)
            ->assertSee('Platform Analytics Dashboard')
            ->assertSee('Total Registered Users')
            ->assertSee('Total Revenue');
    }

    public function test_admin_can_view_users_list_and_details(): void
    {
        $admin = Admin::where('email', 'admin@datingapp.com')->first();
        $user = User::factory()->create([
            'name' => 'Test Profile User',
            'email' => 'profileuser@test.com',
        ]);

        $resList = $this->actingAs($admin, 'admin')->get('/admin/users');
        $resList->assertStatus(200)
            ->assertSee('Registered Dating Profiles')
            ->assertSee('Test Profile User');

        $resShow = $this->actingAs($admin, 'admin')->get("/admin/users/{$user->id}");
        $resShow->assertStatus(200)
            ->assertSee('Test Profile User')
            ->assertSee('Star Balance');
    }

    public function test_admin_can_toggle_user_verification_badge(): void
    {
        $admin = Admin::where('email', 'admin@datingapp.com')->first();
        $user = User::factory()->create(['is_verified' => false]);

        $this->actingAs($admin, 'admin')
            ->post("/admin/users/{$user->id}/toggle-verify");

        $this->assertTrue($user->fresh()->is_verified);
    }

    public function test_admin_can_adjust_user_stars(): void
    {
        $admin = Admin::where('email', 'admin@datingapp.com')->first();
        $user = User::factory()->create();
        $wallet = $user->getOrCreateWallet();
        $initialBalance = $wallet->balance;

        $this->actingAs($admin, 'admin')
            ->post("/admin/users/{$user->id}/adjust-stars", [
                'action' => 'credit',
                'amount' => 50,
                'note' => 'Test bonus credit from admin',
            ]);

        $this->assertEquals($initialBalance + 50, $wallet->fresh()->balance);
    }

    public function test_admin_can_create_and_manage_subscription_plans(): void
    {
        $admin = Admin::where('email', 'admin@datingapp.com')->first();

        $response = $this->actingAs($admin, 'admin')
            ->post('/admin/subscriptions', [
                'name' => 'Diamond Elite VIP',
                'description' => 'Top tier VIP perks',
                'price' => 1999.00,
                'currency' => 'INR',
                'duration_days' => 60,
                'daily_swipe_limit' => -1,
                'daily_star_allowance' => 20,
                'badge_color' => '#6366f1',
                'can_see_likes' => 1,
                'can_rewind' => 1,
                'can_boost' => 1,
                'is_active' => 1,
            ]);

        $response->assertRedirect('/admin/subscriptions');
        $this->assertDatabaseHas('subscription_plans', ['name' => 'Diamond Elite VIP']);
    }

    public function test_admin_can_export_transactions_csv(): void
    {
        $admin = Admin::where('email', 'admin@datingapp.com')->first();

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/payments/export/csv');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
