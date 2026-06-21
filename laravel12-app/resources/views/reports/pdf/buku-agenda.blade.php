<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Agenda {{ ucfirst($type) }} - {{ $periode }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 5px 0; }
        .header p { margin: 3px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #333; padding: 6px; font-size: 10pt; }
        th { background-color: #f0f0f0; text-align: center; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer { margin-top: 30px; font-size: 9pt; text-align: right; }
        .page-number { page-break-after: always; }
    </style>
</head>
<body>
    <div class="header">
        <h3>{{ strtoupper($instansi) }}</h3>
        <h2>BUKU AGENDA SURAT {{ strtoupper($type) }}</h2>
        <p>Periode: {{ \Carbon\Carbon::parse($periode)->format('F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                @if($type === 'masuk')
                <th width="10%">Agenda</th>
                <th width="12%">Tgl Terima</th>
                <th width="15%">Nomor Surat</th>
                <th>Pengirim</th>
                <th>Perihal</th>
                <th width="12%">Klasifikasi</th>
                <th width="8%">Prioritas</th>
                @else
                <th width="15%">Nomor Surat</th>
                <th width="12%">Tanggal Surat</th>
                <th>Unit Pembuat</th>
                <th>Tujuan</th>
                <th>Perihal</th>
                <th width="12%">Status</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                @if($type === 'masuk')
                <td class="text-center">{{ $item->agenda ?? '-' }}</td>
                <td class="text-center">{{ $item->tanggal_terima ? $item->tanggal_terima->format('d/m/Y') : '-' }}</td>
                <td>{{ $item->nomor_surat ?? '-' }}</td>
                <td>{{ $item->pengirim ?? '-' }}</td>
                <td>{{ Str::limit($item->perihal, 40) }}</td>
                <td>{{ $item->klasifikasi->nama_klasifikasi ?? '-' }}</td>
                <td class="text-center">{{ $item->prioritas ?? 'Normal' }}</td>
                @else
                <td>{{ $item->nomor_surat_final ?? '-' }}</td>
                <td class="text-center">{{ $item->tanggal_surat_final ? $item->tanggal_surat_final->format('d/m/Y') : '-' }}</td>
                <td>{{ $item->unitPembuat->nama_unit ?? '-' }}</td>
                <td>{{ $item->tujuan ?? '-' }}</td>
                <td>{{ Str::limit($item->perihal, 40) }}</td>
                <td class="text-center">{{ $item->status ?? 'Draft' }}</td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada: {{ $generatedAt }}</p>
    </div>
</body>
</html>
