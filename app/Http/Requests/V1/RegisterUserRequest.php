<?php

namespace App\Http\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ];
    }

    /**
     * Get the body parameters for API documentation
     */
    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'The name of the user',
                'example' => 'John Doe',
            ],
            'email' => [
                'description' => 'The email address of the user',
                'example' => 'john@example.com',
            ],
            'password' => [
                'description' => 'The password for the user account (min 8 characters)',
                'example' => 'secret-password',
            ],
            'password_confirmation' => [
                'description' => 'Password confirmation that must match the password',
                'example' => 'secret-password',
            ],
        ];
    }
}
