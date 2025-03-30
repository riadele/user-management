<?php

namespace Tests\Feature;

use App\Http\Requests\LoginRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class LoginRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the authorization method of the LoginRequest.
     *
     * @return void
     */
    public function testAuthorizeReturnsTrue()
    {
        $loginRequest = new LoginRequest();
        
        // Test that the authorize method returns true.
        $this->assertTrue($loginRequest->authorize());
    }

    /**
     * Test the validation rules for the login request.
     *
     * @return void
     */
    public function testValidationRules()
    {
        $request = new LoginRequest();

        // Valid input data
        $data = [
            'email' => 'user@example.com',
            'password' => 'PassWord@123',
            'remember' => true
        ];

        // Test valid data passes the validation
        // $validator = Validator::make($data, $request->rules());
        // $this->assertFalse($validator->fails());

        // Test missing email
        $data['email'] = '';
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());

        // Test invalid email format
        $data['email'] = 'invalid-email';
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());

        // Test missing password
        $data['email'] = 'user@example.com';
        $data['password'] = '';
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());

        // Test valid password
        // $data['password'] = 'PassWord@123';
        // $validator = Validator::make($data, $request->rules());
        // $this->assertFalse($validator->fails());

        // Test invalid remember field (non-boolean value)
        $data['remember'] = 'not-a-boolean';
        $validator = Validator::make($data, $request->rules());
        $this->assertTrue($validator->fails());

        // Test valid remember value (boolean)
        // $data['remember'] = true;
        // $validator = Validator::make($data, $request->rules());
        // $this->assertFalse($validator->fails());
    }
}
