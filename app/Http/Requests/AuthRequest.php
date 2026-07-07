<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AuthRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // register
        if ($this->routeIs('register')) {
            return [
                "name" => 'required|string|max:50',
                "email" => 'required|email|unique:users,email',
                "password" => 'required|string|min:8',
            ];
        }

        // login
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your name.',
            'name.max' => 'The name can not be more than 50 characters',
            'email.required' => 'Please enter a valid email.',
            'email.unique' => 'This email is already registered.',
            'password.required' => 'Please enter a valid password.',
            'password.min' => 'The password can not be less than 8 characters',
            
        ];
    }
}
