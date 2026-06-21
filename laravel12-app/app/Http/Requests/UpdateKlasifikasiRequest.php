<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKlasifikasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode' => ['required', 'string', 'max:20', Rule::unique('klasifikasi_arsip')->ignore($this->klasifikasi)],
            'nama' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:klasifikasi_arsip,id'],
            'retensi_aktif' => ['required', 'integer', 'min:1'],
            'retensi_inaktif' => ['required', 'integer', 'min:1'],
        ];
    }
}
