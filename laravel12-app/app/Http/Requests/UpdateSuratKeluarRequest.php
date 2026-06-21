<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateSuratKeluarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && $this->route('suratKeluar')->canEdit();
    }

    public function rules(): array
    {
        return [
            'tujuan' => ['required', 'string', 'max:255'],
            'perihal' => ['required', 'string', 'max:255'],
            'isi_ringkas' => ['required', 'string', 'max:1000'],
            'klasifikasi_id' => ['required', 'exists:klasifikasi_arsip,id'],
            'sifat_id' => ['required', 'exists:sifat_surats,id'],
        ];
    }
}
