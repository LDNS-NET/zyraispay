<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Validation\Validator;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $user = $this->user();

        $emailChanged = $this->email !== $user->email;
        $phoneChanged = $this->phone !== $user->phone;
        $business_nameChanged = $this->business_name !== $user->business_name;

        $requirePassword = $emailChanged || $phoneChanged || $business_nameChanged;

        return [
            'business_name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],

            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique(User::class, 'phone')->ignore($user->id),
            ],

            'current_password' => array_filter([
                $requirePassword ? 'required' : null,
                $requirePassword ? 'current_password' : null,
            ]),
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Please enter your current password to change profile.',
            'current_password.current_password' => 'The current password is incorrect.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function (Validator $validator) {
            $user = $this->user();

            $emailChanged = $this->email !== $user->email;
            $phoneChanged = $this->phone !== $user->phone;
            $business_nameChanged = $this->business_name !== $user->business_name;

            if (($emailChanged || $phoneChanged || $business_nameChanged) && !$this->filled('current_password')) {
                $validator->errors()->add('current_password', 'Your current password is required to make these changes.');
            }

            if ($this->filled('current_password') && !Hash::check($this->current_password, $user->password)) {
                $validator->errors()->add('current_password', 'The provided password is incorrect.');
            }
        });
    }
}
