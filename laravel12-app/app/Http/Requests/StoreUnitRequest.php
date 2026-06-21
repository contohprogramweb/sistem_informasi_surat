<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode_unit' => ['required', 'string', 'min:3', 'max:10', 'unique:units,kode_unit', 'regex:/^[A-Z0-9]+$/'],
            'nama_unit' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode_unit.regex' => 'Kode unit harus huruf besar dan angka.',
            'kode_unit.unique' => 'Kode unit sudah digunakan.',
            'kode_unit.min' => 'Kode unit minimal 3 karakter.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'kode_unit' => strtoupper($this->kode_unit),
        ]);
    }
}
