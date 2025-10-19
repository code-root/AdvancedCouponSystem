<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use App\Events\SubscriptionCreated;
use App\Events\SubscriptionCancelled;
use App\Events\SubscriptionUpgraded;
use App\Events\AdminSessionStarted;
use App\Models\AdminSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SubscriptionEventTest extends TestCase
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
    public function subscription_created_event_is_dispatched()
    {
        Event::fake();

        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
        ]);

        event(new SubscriptionCreated($subscription));

        Event::assertDispatched(SubscriptionCreated::class, function ($event) use ($subscription) {
            return $event->subscription->id === $subscription->id;
        });
    }

    /** @test */
    public function subscription_cancelled_event_is_dispatched()
    {
        Event::fake();

        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
            'status' => 'canceled',
        ]);

        event(new SubscriptionCancelled($subscription, 'User requested cancellation'));

        Event::assertDispatched(SubscriptionCancelled::class, function ($event) use ($subscription) {
            return $event->subscription->id === $subscription->id &&
                   $event->reason === 'User requested cancellation';
        });
    }

    /** @test */
    public function subscription_upgraded_event_is_dispatched()
    {
        Event::fake();

        $oldPlan = Plan::factory()->create(['name' => 'Basic Plan']);
        $newPlan = Plan::factory()->create(['name' => 'Premium Plan']);
        
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $newPlan->id,
        ]);

        event(new SubscriptionUpgraded($subscription, $oldPlan, $newPlan));

        Event::assertDispatched(SubscriptionUpgraded::class, function ($event) use ($subscription, $oldPlan, $newPlan) {
            return $event->subscription->id === $subscription->id &&
                   $event->oldPlan->id === $oldPlan->id &&
                   $event->newPlan->id === $newPlan->id;
        });
    }

    /** @test */
    public function admin_session_started_event_is_dispatched()
    {
        Event::fake();

        $adminSession = AdminSession::factory()->create([
            'admin_id' => $this->admin->id,
        ]);

        event(new AdminSessionStarted($this->admin, [
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
            'device_name' => 'Test Device',
            'platform' => 'Test Platform',
            'browser' => 'Test Browser',
        ]));

        Event::assertDispatched(AdminSessionStarted::class, function ($event) {
            return $event->admin->id === $this->admin->id;
        });
    }

    /** @test */
    public function subscription_created_event_broadcasts_to_correct_channels()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
        ]);

        $event = new SubscriptionCreated($subscription);
        $channels = $event->broadcastOn();

        $this->assertCount(2, $channels);
        $this->assertInstanceOf(\Illuminate\Broadcasting\PrivateChannel::class, $channels[0]);
        $this->assertInstanceOf(\Illuminate\Broadcasting\Channel::class, $channels[1]);
    }

    /** @test */
    public function subscription_cancelled_event_broadcasts_to_correct_channels()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
        ]);

        $event = new SubscriptionCancelled($subscription, 'User requested cancellation');
        $channels = $event->broadcastOn();

        $this->assertCount(2, $channels);
        $this->assertInstanceOf(\Illuminate\Broadcasting\PrivateChannel::class, $channels[0]);
        $this->assertInstanceOf(\Illuminate\Broadcasting\Channel::class, $channels[1]);
    }

    /** @test */
    public function subscription_upgraded_event_broadcasts_to_correct_channels()
    {
        $oldPlan = Plan::factory()->create(['name' => 'Basic Plan']);
        $newPlan = Plan::factory()->create(['name' => 'Premium Plan']);
        
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $newPlan->id,
        ]);

        $event = new SubscriptionUpgraded($subscription, $oldPlan, $newPlan);
        $channels = $event->broadcastOn();

        $this->assertCount(2, $channels);
        $this->assertInstanceOf(\Illuminate\Broadcasting\PrivateChannel::class, $channels[0]);
        $this->assertInstanceOf(\Illuminate\Broadcasting\Channel::class, $channels[1]);
    }

    /** @test */
    public function admin_session_started_event_broadcasts_to_correct_channels()
    {
        $adminSession = AdminSession::factory()->create([
            'admin_id' => $this->admin->id,
        ]);

        $event = new AdminSessionStarted($this->admin, [
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
            'device_name' => 'Test Device',
            'platform' => 'Test Platform',
            'browser' => 'Test Browser',
        ]);
        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(\Illuminate\Broadcasting\Channel::class, $channels[0]);
    }

    /** @test */
    public function subscription_created_event_broadcasts_correct_data()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
        ]);

        $event = new SubscriptionCreated($subscription);
        $data = $event->broadcastWith();

        $this->assertEquals($subscription->id, $data['subscription_id']);
        $this->assertEquals($this->user->name, $data['user_name']);
        $this->assertEquals($this->plan->name, $data['plan_name']);
        $this->assertEquals($subscription->status, $data['status']);
        $this->assertArrayHasKey('message', $data);
    }

    /** @test */
    public function subscription_cancelled_event_broadcasts_correct_data()
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $this->plan->id,
        ]);

        $event = new SubscriptionCancelled($subscription, 'User requested cancellation');
        $data = $event->broadcastWith();

        $this->assertEquals($subscription->id, $data['subscription_id']);
        $this->assertEquals($this->user->name, $data['user_name']);
        $this->assertEquals($this->plan->name, $data['plan_name']);
        $this->assertEquals($subscription->status, $data['status']);
        $this->assertEquals('User requested cancellation', $data['reason']);
        $this->assertArrayHasKey('message', $data);
    }

    /** @test */
    public function subscription_upgraded_event_broadcasts_correct_data()
    {
        $oldPlan = Plan::factory()->create(['name' => 'Basic Plan']);
        $newPlan = Plan::factory()->create(['name' => 'Premium Plan']);
        
        $subscription = Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $newPlan->id,
        ]);

        $event = new SubscriptionUpgraded($subscription, $oldPlan, $newPlan);
        $data = $event->broadcastWith();

        $this->assertEquals($subscription->id, $data['subscription_id']);
        $this->assertEquals($this->user->name, $data['user_name']);
        $this->assertEquals($oldPlan->name, $data['old_plan_name']);
        $this->assertEquals($newPlan->name, $data['new_plan_name']);
        $this->assertArrayHasKey('message', $data);
    }

    /** @test */
    public function admin_session_started_event_broadcasts_correct_data()
    {
        $adminSession = AdminSession::factory()->create([
            'admin_id' => $this->admin->id,
        ]);

        $event = new AdminSessionStarted($this->admin, [
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test Agent',
            'device_name' => 'Test Device',
            'platform' => 'Test Platform',
            'browser' => 'Test Browser',
        ]);
        $data = $event->broadcastWith();

        $this->assertEquals($this->admin->id, $data['admin']['id']);
        $this->assertEquals($this->admin->name, $data['admin']['name']);
        $this->assertEquals('127.0.0.1', $data['session']['ip_address']);
        $this->assertEquals('Test Device', $data['session']['device_name']);
        $this->assertEquals('Test Platform', $data['session']['platform']);
        $this->assertEquals('Test Browser', $data['session']['browser']);
    }
}

