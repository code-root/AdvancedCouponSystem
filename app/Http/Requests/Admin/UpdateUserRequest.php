<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->guard('admin')->check() && 
               auth()->guard('admin')->user()->hasPermissionTo('manage-users', 'admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('id') ?? $this->route('user');
        
        return [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => 'nullable|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            'status' => 'required|in:active,inactive,suspended',
            'plan_id' => 'nullable|exists:plans,id',
            'phone' => 'nullable|string|max:20|regex:/^[\+]?[1-9][\d]{0,15}$/',
            'timezone' => 'nullable|string|max:255|in:' . implode(',', timezone_identifiers_list()),
            'bio' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'User name is required.',
            'name.regex' => 'Name can only contain letters and spaces.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'status.required' => 'User status is required.',
            'status.in' => 'Invalid user status selected.',
            'plan_id.exists' => 'Selected plan does not exist.',
            'phone.regex' => 'Please provide a valid phone number.',
            'timezone.in' => 'Invalid timezone selected.',
            'bio.max' => 'Bio cannot exceed 500 characters.',
            'avatar.image' => 'Avatar must be an image file.',
            'avatar.mimes' => 'Avatar must be a JPEG, PNG, JPG, or GIF file.',
            'avatar.max' => 'Avatar file size cannot exceed 2MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'user name',
            'email' => 'email address',
            'password' => 'password',
            'status' => 'user status',
            'plan_id' => 'plan',
            'phone' => 'phone number',
            'timezone' => 'timezone',
            'bio' => 'bio',
            'avatar' => 'avatar',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->name),
            ]);
        }

        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim($this->email)),
            ]);
        }

        if ($this->has('phone')) {
            $this->merge([
                'phone' => preg_replace('/[^0-9+]/', '', $this->phone),
            ]);
        }
    }
}

