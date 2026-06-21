<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Enums\SuratKeluarStatus;

class TransitionSuratKeluarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:draft,review,disetujui,ditolak,siap_ttd,tertandatangani,terkirim'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function getNewStatus(): SuratKeluarStatus
    {
        return SuratKeluarStatus::from($this->input('status'));
    }
}
