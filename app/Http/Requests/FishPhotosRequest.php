<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FishPhotosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['before' => ['nullable', 'integer', 'min:1', 'max:'.PHP_INT_MAX]];
    }
}
