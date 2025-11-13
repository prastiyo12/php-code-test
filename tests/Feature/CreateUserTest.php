<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeUser;
use App\Mail\NotifyAdmin;

class CreateUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_user_sends_emails_and_returns_201()
    {
        Mail::fake();

        $payload = [
            'email' => 'alice@example.com',
            'password' => 'password123',
            'name' => 'Alice',
            'role' => 'user',
        ];

        $this->postJson('/api/v1/users', $payload)
            ->assertStatus(201)
            ->assertJsonFragment([
                'email' => 'alice@example.com',
                'name' => 'Alice',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'alice@example.com',
            'name' => 'Alice',
        ]);

        Mail::assertSent(WelcomeUser::class);
        Mail::assertSent(NotifyAdmin::class);
    }
}
