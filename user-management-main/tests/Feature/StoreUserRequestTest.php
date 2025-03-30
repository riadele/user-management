<?php

namespace Tests\Feature;

use App\Http\Requests\StoreUserRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use App\Models\User;

class StoreUserRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the authorization method of the StoreUserRequest.
     *
     * @return void
     */
    public function testAuthorizeReturnsTrue()
    {
        $storeUserRequest = new StoreUserRequest();
        
        // Test that the authorize method returns true.
        $this->assertTrue($storeUserRequest->authorize());
    }

    /**
     * Test the validation rules for the store user request.
     *
     * @return void
     */
    public function testValidationRules()
    {
        $request = new StoreUserRequest();

        // Valid input data
        $data = [
            'name' => 'John Doe',
            'email' => 'user@example.com',
            'password' => 'Password123!',
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
        $data['name'] = 'John Doe';
        $data['email'] = '';
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());

        // Test invalid email format
        $data['email'] = 'invalid-email';
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());

        // Test existing email (for unique validation)
        $data['email'] = 'user@example.com'; // This email should already be in the database for testing "unique" rule
        User::create([
            'name' => 'Existing User',
            'email' => 'user@example.com',
            'password' => bcrypt('existingpassword')
        ]);
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());

        // Test missing password
        $data['email'] = 'newuser@example.com';
        $data['password'] = '';
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());

        // Test weak password (less than 8 characters)
        $data['password'] = 'short';
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());

        // Test password with letters and symbols but not enough length
        $data['password'] = 'Short!1';
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());

        // Test password with letters, symbols, and enough length
        $data['password'] = 'ValidPassword123!';
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->fails());
    }
}
