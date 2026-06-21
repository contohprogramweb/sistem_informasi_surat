<?php

namespace App\Enums;

enum SuratKeluarStatus: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Disetujui = 'disetujui';
    case Ditolak = 'ditolak';
    case SiapTandatangan = 'siap_ttd';
    case Tertandatangani = 'tertandatangani';
    case Terkirim = 'terkirim';

    public function label(): string
    {
        return match($this) {
            self::Draft => 'Draft',
            self::Review => 'Dalam Review',
            self::Disetujui => 'Disetujui',
            self::Ditolak => 'Ditolak',
            self::SiapTandatangan => 'Siap Tanda Tangan',
            self::Tertandatangani => 'Tertandatangani',
            self::Terkirim => 'Terkirim',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft => 'secondary',
            self::Review => 'warning',
            self::Disetujui => 'success',
            self::Ditolak => 'danger',
            self::SiapTandatangan => 'info',
            self::Tertandatangani => 'primary',
            self::Terkirim => 'success',
        };
    }

    public function canTransitionTo(self $nextStatus): bool
    {
        $allowedTransitions = [
            self::Draft => [self::Review, self::Ditolak],
            self::Review => [self::Disetujui, self::Ditolak, self::SiapTandatangan],
            self::Disetujui => [self::SiapTandatangan, self::Ditolak],
            self::Ditolak => [self::Draft],
            self::SiapTandatangan => [self::Tertandatangani, self::Ditolak],
            self::Tertandatangani => [self::Terkirim],
            self::Terkirim => [],
        ];

        return in_array($nextStatus, $allowedTransitions[$this] ?? []);
    }
}
