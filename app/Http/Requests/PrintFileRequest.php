<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrintFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'format' => 'nullable|in:pdf',
        ];
    }
}
