<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;

class ListUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_users_returns_orders_count_and_can_edit()
    {
        // create actor (manager)
        $actor = User::factory()->create([
            'role' => 'manager',
        ]);

        // create users with orders
        $userA = User::factory()->create(['role' => 'user', 'name' => 'User A']);
        Order::factory()->create(['user_id' => $userA->id]);
        Order::factory()->create(['user_id' => $userA->id]);

        $userB = User::factory()->create(['role' => 'user', 'name' => 'User B']);

        $this->actingAs($actor)
            ->getJson('/api/v1/users')
            ->assertStatus(200)
            ->assertJsonStructure([
                'page',
                'per_page',
                'total',
                'users' => [
                    ['id','email','name','role','created_at','orders_count','can_edit']
                ],
            ]);
    }
}
