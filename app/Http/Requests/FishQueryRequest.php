<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FishQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'q' => [$this->routeIs('fish.search') ? 'required' : 'nullable', 'string', 'min:1', 'max:150'],
            'page' => ['nullable', 'integer', 'min:1', 'max:5000'],
        ];
    }
}
