<?php

namespace Tests\Feature;

use App\Http\Requests\UpdateUserRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UpdateUserRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the authorization method of the UpdateUserRequest.
     *
     * @return void
     */
    public function testAuthorizeReturnsTrue()
    {
        $updateUserRequest = new UpdateUserRequest();
        
        // Test that the authorize method returns true.
        $this->assertTrue($updateUserRequest->authorize());
    }
    /**
     * Test the validation rules for the update user request.
     *
     * @return void
     */
    public function testValidationRules()
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'user@example.com',
            'password' => bcrypt('Password123!'),
        ]);

        $request = new UpdateUserRequest();

        // Valid input data (excluding password to test rules properly)
        $data = [
            'name' => 'John Doe Updated',
            'email' => 'userupdated@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        // Test valid data passes the validation
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->fails());

        // Test missing name
        $data['name'] = '';
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());

        // Test name exceeding max length (55 characters)
        $data['name'] = str_repeat('A', 56);
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());

        // Test missing email
        $data['name'] = 'John Doe Updated';
        $data['email'] = '';
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());

        // Test invalid email format
        $data['email'] = 'invalid-email';
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());

        // Test email already exists (unique rule should not fail for the current user)
        // $data['email'] = 'user@example.com'; // Email of the user we're updating
        // $data['id'] = $user->id;
        // $validator = Validator::make($data, $request->rules());
        // $this->assertFalse($validator->fails()); // Same email should not fail

        // Test email that already exists (not the current user)
        $anotherUser = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt('Password123!'),
        ]);
        $data['email'] = 'jane@example.com'; // Different email already in database
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails()); // Email should fail uniqueness check

        // Test missing password
        $data['email'] = 'newuser@example.com';
        $data['password'] = '';
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->fails()); // Password is not required for update (confirmation is)

        // Test weak password (less than 8 characters)
        $data['password'] = 'short';
        $data['password_confirmation'] = 'short';
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());

        // Test password with letters and symbols but not enough length
        $data['password'] = 'Short!1';
        $data['password_confirmation'] = 'Short!1';
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());

        // Test valid password
        $data['password'] = 'Password123!';
        $data['password_confirmation'] = 'Password123!';
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->fails());
    }
}
