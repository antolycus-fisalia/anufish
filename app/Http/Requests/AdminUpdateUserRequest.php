<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        $targetUser = $this->route('user');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($targetUser),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('admin_users.validation.name_required'),
            'name.string' => __('admin_users.validation.name_string'),
            'name.max' => __('admin_users.validation.name_max'),
            'email.required' => __('admin_users.validation.email_required'),
            'email.email' => __('admin_users.validation.email_invalid'),
            'email.max' => __('admin_users.validation.email_max'),
            'email.unique' => __('admin_users.validation.email_unique'),
        ];
    }
}
