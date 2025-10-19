<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use App\Notifications\NewSubscriptionNotification;
use App\Notifications\SubscriptionCancelledNotification;
use App\Notifications\SubscriptionUpgradedNotification;
use App\Notifications\SubscriptionExpiringSoonNotification;
use App\Notifications\ManualSubscriptionActivatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SubscriptionNotificationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $user;
    protected $plan;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = Admin::factory()->create(['active' => true]);
        $this->user = User::factory()->create();
        $this->plan = Plan::factory()->create();
    }

    /** @test */
    public function new_subscription_notification_is_sent_to_admin()
    {
        Notification::fake();

        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
        ]);

        // Simulate the notification being sent
        $this->admin->notify(new NewSubscriptionNotification($subscription));

        Notification::assertSentTo(
            $this->admin,
            NewSubscriptionNotification::class,
            function ($notification) use ($subscription) {
                return $notification->subscription->id === $subscription->id;
            }
        );
    }

    /** @test */
    public function subscription_cancelled_notification_is_sent_to_admin()
    {
        Notification::fake();

        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'canceled',
        ]);

        $this->admin->notify(new SubscriptionCancelledNotification($subscription, 'User requested cancellation'));

        Notification::assertSentTo(
            $this->admin,
            SubscriptionCancelledNotification::class,
            function ($notification) use ($subscription) {
                return $notification->subscription->id === $subscription->id &&
                       $notification->reason === 'User requested cancellation';
            }
        );
    }

    /** @test */
    public function subscription_upgraded_notification_is_sent_to_admin()
    {
        Notification::fake();

        $oldPlan = Plan::factory()->create(['name' => 'Basic Plan']);
        $newPlan = Plan::factory()->create(['name' => 'Premium Plan']);
        
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $newPlan->id,
        ]);

        $this->admin->notify(new SubscriptionUpgradedNotification($subscription, $oldPlan, $newPlan));

        Notification::assertSentTo(
            $this->admin,
            SubscriptionUpgradedNotification::class,
            function ($notification) use ($subscription, $oldPlan, $newPlan) {
                return $notification->subscription->id === $subscription->id &&
                       $notification->oldPlan->id === $oldPlan->id &&
                       $notification->newPlan->id === $newPlan->id;
            }
        );
    }

    /** @test */
    public function subscription_expiring_soon_notification_is_sent_to_user()
    {
        Notification::fake();

        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'ends_at' => now()->addDays(3),
        ]);

        $this->user->notify(new SubscriptionExpiringSoonNotification($subscription, 3));

        Notification::assertSentTo(
            $this->user,
            SubscriptionExpiringSoonNotification::class,
            function ($notification) use ($subscription) {
                return $notification->subscription->id === $subscription->id &&
                       $notification->daysRemaining === 3;
            }
        );
    }

    /** @test */
    public function manual_subscription_activated_notification_is_sent_to_admin()
    {
        Notification::fake();

        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
        ]);

        $this->admin->notify(new ManualSubscriptionActivatedNotification($subscription, $this->admin->name));

        Notification::assertSentTo(
            $this->admin,
            ManualSubscriptionActivatedNotification::class,
            function ($notification) use ($subscription) {
                return $notification->subscription->id === $subscription->id &&
                       $notification->adminName === $this->admin->name;
            }
        );
    }

    /** @test */
    public function notifications_are_sent_via_multiple_channels()
    {
        Notification::fake();

        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
        ]);

        $notification = new NewSubscriptionNotification($subscription);
        $channels = $notification->via($this->admin);

        $this->assertContains('database', $channels);
        $this->assertContains('broadcast', $channels);
        $this->assertContains('mail', $channels);
    }

    /** @test */
    public function notification_data_is_correctly_formatted()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
        ]);

        $notification = new NewSubscriptionNotification($subscription);
        $data = $notification->toArray($this->admin);

        $this->assertEquals($subscription->id, $data['subscription_id']);
        $this->assertEquals($this->user->id, $data['user_id']);
        $this->assertEquals($this->user->name, $data['user_name']);
        $this->assertEquals($this->plan->name, $data['plan_name']);
        $this->assertEquals($subscription->status, $data['status']);
        $this->assertArrayHasKey('message', $data);
    }

    /** @test */
    public function broadcast_notification_data_is_correctly_formatted()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
        ]);

        $notification = new NewSubscriptionNotification($subscription);
        $broadcastData = $notification->toBroadcast($this->admin);

        $this->assertEquals($subscription->id, $broadcastData->data['subscription_id']);
        $this->assertEquals($this->user->id, $broadcastData->data['user_id']);
        $this->assertEquals($this->user->name, $broadcastData->data['user_name']);
        $this->assertEquals($this->plan->name, $broadcastData->data['plan_name']);
        $this->assertEquals($subscription->status, $broadcastData->data['status']);
        $this->assertArrayHasKey('message', $broadcastData->data);
    }
}

