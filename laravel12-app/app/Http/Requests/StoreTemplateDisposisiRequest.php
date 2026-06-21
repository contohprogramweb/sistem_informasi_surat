<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTemplateDisposisiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100'],
            'instruksi_default' => ['required', 'string'],
            'tujuan_default' => ['nullable', 'array'],
            'tujuan_default.*' => ['exists:users,id'],
            'tembusan_default' => ['nullable', 'string', 'max:255'],
        ];
    }
}
