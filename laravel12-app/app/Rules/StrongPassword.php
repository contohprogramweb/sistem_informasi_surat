<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule, DataAwareRule
{
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * Set the data under validation.
     *
     * @param array<string, mixed> $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Minimal 12 karakter
        if (strlen($value) < 12) {
            $fail('Password harus minimal 12 karakter.');
            return;
        }

        // Harus ada huruf besar
        if (!preg_match('/[A-Z]/', $value)) {
            $fail('Password harus mengandung setidaknya satu huruf kapital.');
            return;
        }

        // Harus ada huruf kecil
        if (!preg_match('/[a-z]/', $value)) {
            $fail('Password harus mengandung setidaknya satu huruf kecil.');
            return;
        }

        // Harus ada angka
        if (!preg_match('/[0-9]/', $value)) {
            $fail('Password harus mengandung setidaknya satu angka.');
            return;
        }

        // Harus ada simbol khusus
        if (!preg_match('/[@$!%*?&#]/', $value)) {
            $fail('Password harus mengandung setidaknya satu simbol (@$!%*?&#).');
            return;
        }

        // Tidak boleh mengandung username/email
        $username = $this->data['email'] ?? $this->data['username'] ?? '';
        if (!empty($username)) {
            // Ambil bagian sebelum @ untuk email
            $usernamePart = explode('@', $username)[0];
            
            // Cek apakah password mengandung username (case insensitive)
            if (stripos($value, $usernamePart) !== false && strlen($usernamePart) > 2) {
                $fail('Password tidak boleh mengandung username atau email Anda.');
                return;
            }
        }
    }
}
