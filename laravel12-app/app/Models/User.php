<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'unit_id',
        'role',
        'nip',
        'jabatan',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Helper method untuk cek role
     */
    public function hasRole($role): bool
    {
        if (is_array($role)) {
            return in_array($this->role, $role);
        }
        return $this->role === $role;
    }

    /**
     * Helper method untuk cek permission
     */
    public function canPermission($permission): bool
    {
        return $this->hasPermissionTo($permission);
    }

    /**
     * Cek apakah user adalah admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Cek apakah user adalah pimpinan
     */
    public function isPimpinan(): bool
    {
        return $this->hasRole('pimpinan');
    }

    /**
     * Cek apakah user adalah kabag
     */
    public function isKabag(): bool
    {
        return $this->hasRole('kabag');
    }

    /**
     * Cek apakah user adalah staff TU
     */
    public function isStaffTU(): bool
    {
        return $this->hasRole('staff_tu');
    }

    /**
     * Relasi ke unit
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Relasi ke surat masuk
     */
    public function suratMasuk()
    {
        return $this->hasMany(SuratMasuk::class);
    }

    /**
     * Relasi ke surat keluar
     */
    public function suratKeluar()
    {
        return $this->hasMany(SuratKeluar::class);
    }

    /**
     * Relasi ke disposisi yang diterima
     */
    public function disposisiDiterima()
    {
        return $this->hasMany(Disposisi::class, 'ke_user_id');
    }

    /**
     * Relasi ke delegasi
     */
    public function delegasi()
    {
        return $this->hasMany(Delegasi::class);
    }

    /**
     * Relasi sebagai pengganti delegasi
     */
    public function delegasiPengganti()
    {
        return $this->hasMany(Delegasi::class, 'pengganti_user_id');
    }

    /**
     * Cek apakah user sedang dalam delegasi aktif
     */
    public function hasActiveDelegasi(): bool
    {
        return $this->delegasi()->active()->exists();
    }

    /**
     * Get delegasi aktif
     */
    public function getActiveDelegasi()
    {
        return $this->delegasi()->active()->first();
    }
}
