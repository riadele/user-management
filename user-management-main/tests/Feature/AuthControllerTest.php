<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    // Test signup function
    public function testSignup()
    {
        $data = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'PassWord@123',
            'password_confirmation' => 'PassWord@123'
        ];

        $response = $this->postJson(route('auth.signup'), $data);

        // Assert that the user is created and the response contains the user and token
        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
                'token'
            ]);

        // Assert that the user is stored in the database
        $this->assertDatabaseHas('users', [
            'email' => $data['email']
        ]);
    }

    // Test login function
    public function testLogin()
    {
        $user = User::factory()->create([
            'password' => Hash::make('PassWord@123'),
        ]);

        $data = [
            'email' => $user->email,
            'password' => 'PassWord@123',
        ];

        $response = $this->postJson(route('auth.login'), $data);

        // Assert that the response contains a user and a token
        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
                'token'
            ]);
    }

    // Test login with invalid credentials
    public function testLoginWithInvalidCredentials()
    {
        $user = User::factory()->create([
            'password' => Hash::make('PassWord@123'),
        ]);

        $data = [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson(route('auth.login'), $data);

        // Assert that the status is 422 with the error message
        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Provided email or password is incorrect'
            ]);
    }

    public function testLogout()
    {
        // Step 1: Create a user
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        // Step 2: Act as the authenticated user (using Sanctum)
        Sanctum::actingAs($user);

        // Step 3: Make the logout request
        $response = $this->postJson(route('auth.logout'));

        // Step 4: Assert the response status code is 204 (No Content)
        $response->assertStatus(204);

        // Step 5: Assert the token is deleted from the database
        // Ensure that the personal_access_tokens table no longer contains the user's token
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id, // user ID
            'tokenable_type' => get_class($user), // user model
        ]);
    }
}
