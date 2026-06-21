<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode_unit' => ['required', 'string', 'min:3', 'max:10', Rule::unique('units')->ignore($this->unit), 'regex:/^[A-Z0-9]+$/'],
            'nama_unit' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'kode_unit' => strtoupper($this->kode_unit),
        ]);
    }
}
