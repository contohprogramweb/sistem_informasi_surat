<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSuratMasukRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal_terima' => ['required', 'date'],
            'cara_terima' => ['required', 'in:datang_langsung,pos,kurir,email'],
            'penerima_fisik' => ['nullable', 'string', 'max:255'],
            'nomor_surat' => ['required', 'string', 'max:100'],
            'tanggal_surat' => ['required', 'date'],
            'pengirim' => ['required', 'string', 'max:255'],
            'perihal' => ['required', 'string'],
            'ringkasan' => ['nullable', 'string'],
            'klasifikasi_id' => ['required', 'exists:klasifikasi_arsip,id'],
            'sifat_id' => ['required', 'exists:sifat_surats,id'],
            'prioritas' => ['required', 'in:Rendah,Normal,Tinggi,Segera'],
            'indeks' => ['nullable', 'array'],
            'indeks.*' => ['string', 'max:50'],
            'tidak_perlu_disposisi' => ['boolean'],
            'unit_tujuan' => ['required', 'array', 'min:1'],
            'unit_tujuan.*' => ['exists:units,id'],
            'lampiran' => ['nullable', 'array', 'max:10'],
            'lampiran.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'lampiran.*.mimes' => 'File lampiran harus berformat PDF, JPG, atau PNG.',
            'lampiran.*.max' => 'Ukuran file lampiran maksimal 10MB.',
            'lampiran.max' => 'Maksimal upload 10 file.',
            'unit_tujuan.min' => 'Pilih minimal satu unit tujuan.',
        ];
    }
}
