<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Hasil Pencarian - SIAP-SMK</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .header { text-align: center; margin-bottom: 30px; }
        .date { color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Hasil Pencarian</h2>
        <p class="date">Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Jenis</th>
                <th>Agenda</th>
                <th>Nomor Surat</th>
                <th>Perihal</th>
                <th>Pengirim/Tujuan</th>
                <th>Tanggal</th>
                <th>Prioritas</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ ucfirst($row['jenis']) }}</td>
                <td>{{ $row['agenda'] }}</td>
                <td>{{ strip_tags($row['nomor_surat']) }}</td>
                <td>{{ strip_tags($row['perihal']) }}</td>
                <td>{{ strip_tags($row['pengirim_tujuan']) }}</td>
                <td>{{ $row['tanggal'] ? date('d/m/Y', strtotime($row['tanggal'])) : '-' }}</td>
                <td>{{ $row['prioritas'] }}</td>
                <td>{{ $row['status'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 20px; font-size: 11px; color: #666;">
        Total hasil: {{ count($data) }} surat
    </p>
</body>
</html>