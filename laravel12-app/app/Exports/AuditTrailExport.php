<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AuditTrailExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $data;
    protected $format;

    public function __construct($data, string $format = 'excel')
    {
        $this->data = $data;
        $this->format = $format;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'No',
            'Timestamp',
            'User',
            'Email',
            'Aksi',
            'Entity',
            'Entity ID',
            'IP Address',
            'Perubahan Data'
        ];
    }

    public function map($log): array
    {
        static $no = 0;
        $no++;

        $diffDescription = '';
        if ($log->old_values && $log->new_values) {
            $diffs = [];
            foreach ($log->new_values as $key => $newValue) {
                $oldValue = $log->old_values[$key] ?? null;
                if ($oldValue !== $newValue) {
                    $diffs[] = "{$key}: {$oldValue} → {$newValue}";
                }
            }
            $diffDescription = implode('; ', $diffs);
        }

        return [
            $no,
            $log->created_at->format('d/m/Y H:i:s'),
            $log->user->name ?? 'System',
            $log->user->email ?? '-',
            $log->action,
            class_basename($log->entity_type),
            $log->entity_id ?? '-',
            $log->ip_address ?? '-',
            $diffDescription
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
        return 'Audit Trail';
    }
}
