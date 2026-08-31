<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Monitoring Hak WBP</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .header h1 { font-size: 16px; color: #0f172a; }
        .header p { font-size: 11px; color: #475569; }
        h2 { font-size: 13px; color: #0f172a; margin-bottom: 8px; }
        .periode { margin-bottom: 16px; font-size: 11px; color: #475569; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        th {
            background: #eff6ff;
            color: #1d4ed8;
            text-align: left;
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        td { padding: 7px 10px; border: 1px solid #e2e8f0; }
        tr:nth-child(even) td { background: #f8fafc; }
        .summary { display: flex; gap: 12px; margin-bottom: 18px; }
        .summary .box {
            flex: 1;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 12px;
            text-align: center;
        }
        .summary .num { font-size: 18px; font-weight: bold; color: #0f172a; }
        .summary .label { font-size: 10px; color: #64748b; }
        .text-right { text-align: right; }
        .footer {
            margin-top: 24px;
            text-align: right;
            font-size: 11px;
            color: #475569;
        }
        .footer .sign { margin-top: 48px; }
        .muted { color: #64748b; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $instansi['nama'] ?? config('app.name') }}</h1>
        <p>Laporan Monitoring Pemenuhan Hak Warga Binaan</p>
        <p>{{ $instansi['alamat'] ?? '' }}</p>
    </div>

    <div class="periode">
        Periode: <strong>{{ \Carbon\Carbon::parse($range['start'])->translatedFormat('d F Y') }}</strong> s.d.
        <strong>{{ \Carbon\Carbon::parse($range['end'])->translatedFormat('d F Y') }}</strong>
    </div>

    <div class="summary">
        <div class="box">
            <div class="num">{{ number_format($summary['total']) }}</div>
            <div class="label">Total Pencatatan</div>
        </div>
        <div class="box">
            <div class="num" style="color:#0d9488">{{ number_format($summary['terpenuhi']) }}</div>
            <div class="label">Hak Terpenuhi</div>
        </div>
        <div class="box">
            <div class="num" style="color:#ea580c">{{ number_format($summary['belum']) }}</div>
            <div class="label">Hak Belum Terpenuhi</div>
        </div>
    </div>

    <h2>Rekapitulasi per Layanan</h2>
    <table>
        <thead>
            <tr>
                <th>Layanan</th>
                <th class="text-right">Total</th>
                <th class="text-right">Terpenuhi</th>
                <th class="text-right">Belum</th>
                <th class="text-right">Pemenuhan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($perCategory as $row)
                <tr>
                    <td>{{ $row['nama'] }}</td>
                    <td class="text-right">{{ number_format($row['total']) }}</td>
                    <td class="text-right">{{ number_format($row['terpenuhi']) }}</td>
                    <td class="text-right">{{ number_format($row['belum']) }}</td>
                    <td class="text-right">{{ $row['persen'] }}%</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Detail Pencatatan</h2>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>WBP</th>
                <th>Layanan</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($detailLogs as $log)
                <tr>
                    <td>{{ $log->tanggal->translatedFormat('d M Y') }}</td>
                    <td>{{ $log->wbp?->nama ?? '—' }}</td>
                    <td>{{ $log->category?->nama_layanan ?? '—' }}</td>
                    <td>{{ $log->status->value }}</td>
                    <td>{{ $log->keterangan ?? '' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }}</p>
        <div class="sign">
            <p>Petugas,</p>
            <br><br>
            <p>{{ auth()->user()->name }}</p>
        </div>
    </div>
</body>
</html>
