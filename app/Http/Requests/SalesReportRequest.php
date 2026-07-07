<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalesReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from'   => 'nullable|date',
            'to'     => 'nullable|date|after_or_equal:from',
            'format' => 'nullable|in:json,xlsx,pdf',
        ];
    }
}
