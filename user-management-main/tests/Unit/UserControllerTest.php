<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Mockery;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;  // Ensure the database is rolled back after each test

    /**
     * Test storing a newly created user.
     *
     * @return void
     */

    public function testIndexWithSearchFilter()
    {
        // Create a user and act as that user
        $user = User::factory()->create();

        // Authenticate the user using Sanctum
        $this->actingAs($user, 'sanctum');  // This will act as the logged-in user

        $searchTerm = 'Elem';

        // Create the mock query builder
        $queryBuilderMock = Mockery::mock('Illuminate\Database\Eloquent\Builder');
        // (Mock query builder as before)

        // Mock the User::query() to return the query builder mock
        $userMock = Mockery::mock('App\Models\User');
        $userMock->shouldReceive('query')
            ->andReturn($queryBuilderMock);

        // Inject the mock User model into the app
        $this->app->instance(User::class, $userMock);

        // Act: Perform the request with the search term
        $response = $this->getJson('/api/users?search=' . $searchTerm);

        // Assert: Ensure the response is correct
        $response->assertStatus(200);
        $response->assertJsonCount(3);  // Check that 3 users are returned
    }

    public function testIndexWithNoSearchFilter()
    {
        // Create a user and act as that user
        $user = User::factory()->create();

        // Authenticate the user using Sanctum
        $this->actingAs($user, 'sanctum');  // This will act as the logged-in user

        $searchTerm = '';

        // Create the mock query builder
        $queryBuilderMock = Mockery::mock('Illuminate\Database\Eloquent\Builder');
        // (Mock query builder as before)

        $queryBuilderMock->shouldReceive('paginate')
            ->andReturn(collect([
                User::factory()->make(),
                User::factory()->make(),
                User::factory()->make(),
                User::factory()->make(),
                User::factory()->make(),
            ]));

        // Mock the User::query() to return the query builder mock
        $userMock = Mockery::mock('App\Models\User');
        $userMock->shouldReceive('query')
            ->andReturn($queryBuilderMock);

        // Inject the mock User model into the app
        $this->app->instance(User::class, $userMock);

        // Act: Perform the request with the search term
        $response = $this->getJson('/api/users?search=' . $searchTerm);

        // Assert: Ensure the response is correct
        $response->assertStatus(200);
        // dd(count($response->json()['data']));
        $response->assertJsonCount(1, 'data');
    }

    public function testStoreUser()
    {
        // Create a user and authenticate
        $user = User::factory()->create();

        // Act as the authenticated user
        $this->actingAs($user, 'sanctum');  // Ensure Sanctum authentication is used

        // Define valid user data
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'PassWord@123',
            'password_confirmation' => 'PassWord@123'
        ];

        // Perform the request to store the user
        $response = $this->postJson('/api/users', $userData);

        // Assert the user was created successfully
        $response->assertStatus(201);  // 201 means Created
        // dd($response->content());
        $response->assertJsonStructure([
            'id',
            'name',
            'email',
        ]);
    }

    public function testShowUser()
    {
        // Step 1: Create a user
        $user = User::factory()->create();

        // Act as the authenticated user
        $this->actingAs($user, 'sanctum');  // Ensure Sanctum authentication is used

        // Step 2: Make the request to the show endpoint (using the user ID)
        $response = $this->getJson("/api/users/{$user->id}");

        // Step 3: Assert that the response status is 200 OK
        $response->assertStatus(200);

        // Step 4: Assert the response contains the correct structure
        // Assuming the UserResource returns the fields 'id', 'name', 'email'
        $response->assertJsonStructure([
                'id',
                'name',
                'email',
        ]);
        // Step 5: Assert that the data returned matches the created user's data
        $response->assertJson([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
        ]);
    }

    public function testUpdateUser()
    {
        // Step 1: Create a user
        $user = User::factory()->create();

        // Act as the authenticated user
        $this->actingAs($user, 'sanctum');  // Ensure Sanctum authentication is used

        // Step 2: Define the update data (you can update any fields like name, email, password)
        $updatedData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'password' => 'PassWord@123', // Make sure the password is provided
            'password_confirmation' => 'PassWord@123'
        ];

        // Step 3: Make the PUT request to update the user
        $response = $this->putJson("/api/users/{$user->id}", $updatedData);

        // Step 4: Assert that the response status is 200 OK
        $response->assertStatus(200);

        // Step 5: Assert that the response contains the correct structure
        $response->assertJsonStructure([
                'id',
                'name',
                'email',
        ]);

        // Step 6: Assert the data is updated in the database
        $user->refresh(); // Refresh the user instance to get the latest data
        $this->assertEquals($updatedData['name'], $user->name);
        $this->assertEquals($updatedData['email'], $user->email);
        // Verify the password is hashed correctly
        $this->assertTrue(Hash::check($updatedData['password'], $user->password));
    }

    public function testUpdateUserValidationError()
    {
        // Step 1: Create a user
        $user = User::factory()->create();

        // Act as the authenticated user
        $this->actingAs($user, 'sanctum');  // Ensure Sanctum authentication is used

        // Step 2: Define invalid update data (e.g., missing required fields)
        $invalidData = [
            'name' => '',  // Invalid name (empty)
            'email' => 'invalid-email', // Invalid email format
        ];

        // Step 3: Make the PUT request to update the user with invalid data
        $response = $this->putJson("/api/users/{$user->id}", $invalidData);

        // Step 4: Assert that the response status is 422 Unprocessable Entity (validation error)
        $response->assertStatus(422);

        // Step 5: Assert that the response contains validation error messages
        $response->assertJsonValidationErrors(['name', 'email']);
    }

    public function testDestroyUser()
    {
        // Step 1: Create a user
        $user = User::factory()->create();

        // Act as the authenticated user
        $this->actingAs($user, 'sanctum');  // Ensure Sanctum authentication is used

        // Step 2: Make the DELETE request to destroy the user
        $response = $this->deleteJson("/api/users/{$user->id}");

        // Step 3: Assert that the response status is 204 No Content
        $response->assertStatus(204);

        // Step 4: Assert that the user is deleted from the database
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
    public function testDestroyUserNotFound()
    {
        // Step 1: Create a user
        $user = User::factory()->create();
        // Act as the authenticated user
        $this->actingAs($user, 'sanctum');  // Ensure Sanctum authentication is used
        // Step 1: Try to delete a user that doesn't exist
        $response = $this->deleteJson("/api/users/99999"); // ID that does not exist

        // Step 2: Assert that the response status is 404 Not Found
        $response->assertStatus(404);
    }
}
