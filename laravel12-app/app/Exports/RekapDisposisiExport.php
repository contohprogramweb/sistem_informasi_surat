<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapDisposisiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nomor Agenda',
            'Tanggal Disposisi',
            'Dari',
            'Kepada',
            'Unit',
            'Perihal',
            'Instruksi',
            'Status',
            'Batas Waktu',
            'Keterangan'
        ];
    }

    public function map($disposisi): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $disposisi->suratMasuk->agenda ?? '-',
            $disposisi->created_at->format('d/m/Y'),
            $disposisi->dariUser->name ?? '-',
            $disposisi->keUser->name ?? '-',
            $disposisi->keUser->unit->nama_unit ?? '-',
            $disposisi->suratMasuk->perihal ?? '-',
            implode(', ', $disposisi->instruksi ?? []),
            $disposisi->status,
            $disposisi->batas_waktu ? $disposisi->batas_waktu->format('d/m/Y') : '-',
            $disposisi->komentar_selesai ?? ($disposisi->isOverdue() ? 'OVERDUE' : '-')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['color' => ['rgb' => 'CCCCCC']]],
        ];
    }

    public function title(): string
    {
        return 'Rekap Disposisi';
    }
}
