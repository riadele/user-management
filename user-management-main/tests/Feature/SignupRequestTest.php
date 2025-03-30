<?php

namespace Tests\Feature;

use App\Http\Requests\SignupRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SignupRequestTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test the authorization method of the SignupRequest.
     *
     * @return void
     */
    public function testAuthorizeReturnsTrue()
    {
        $signupRequest = new SignupRequest();
        
        // Test that the authorize method returns true.
        $this->assertTrue($signupRequest->authorize());
    }

    /**
     * Test the validation rules for the signup request.
     *
     * @return void
     */
    public function testValidationRules()
    {
        $request = new SignupRequest();

        // Valid input data
        $data = [
            'name' => 'John Doe',
            'email' => 'user@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ];

        // Test valid data passes the validation
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->fails());

        // Test missing name
        $data['name'] = '';
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

        // Test existing email
        $data['email'] = 'user@example.com'; // This email should already be in the database for testing "unique" rule
        \App\Models\User::create([
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

        // Test password confirmation mismatch
        $data['password'] = 'Password123!';
        $data['password_confirmation'] = 'Password1234!';
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());

        // Test weak password (does not meet all requirements)
        $data['password_confirmation'] = 'Password123!';
        $data['password'] = 'password';
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());

        // Test strong password
        $data['password'] = 'Password123!';
        $data['password_confirmation'] = 'Password123!';
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->fails());
    }
}
