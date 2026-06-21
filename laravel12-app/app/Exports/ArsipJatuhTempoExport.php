<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ArsipJatuhTempoExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
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
            'Nomor Surat',
            'Tanggal Surat',
            'Pengirim',
            'Perihal',
            'Klasifikasi',
            'Status Arsip',
            'Tanggal Jatuh Tempo',
            'Sisa Hari'
        ];
    }

    public function map($arsip): array
    {
        static $no = 0;
        $no++;

        $jatuhTempo = $arsip->tanggal_jatuh_aktif;
        $sisaHari = $jatuhTempo ? now()->diffInDays($jatuhTempo, false) : '-';

        return [
            $no,
            $arsip->agenda ?? '-',
            $arsip->nomor_surat ?? '-',
            $arsip->tanggal_surat ? $arsip->tanggal_surat->format('d/m/Y') : '-',
            $arsip->pengirim ?? '-',
            $arsip->perihal ?? '-',
            $arsip->klasifikasi->nama_klasifikasi ?? '-',
            $arsip->status_arsip ?? '-',
            $jatuhTempo ? $jatuhTempo->format('d/m/Y') : '-',
            $sisaHari
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
        return 'Arsip Jatuh Tempo';
    }
}
