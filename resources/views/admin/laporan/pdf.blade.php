<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Rekapitulasi Presensi Kehadiran Siswa</title>
    <style>
        @page {
            margin: 12mm 15mm 15mm 15mm;
            size: A4 portrait;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9.5pt;
            color: #1e293b;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        /* Kop Surat Resmi */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .kop-table td {
            vertical-align: middle;
            padding: 0;
        }
        .kop-logo-box {
            width: 60px;
            height: 60px;
            border: 2px solid #166534;
            border-radius: 50%;
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
            font-size: 16pt;
            color: #166534;
            line-height: 56px;
        }
        .kop-emblem-box {
            width: 60px;
            height: 60px;
            background-color: #166534;
            border-radius: 12px;
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
            font-size: 20pt;
            color: #ffffff;
            line-height: 60px;
        }
        .kop-text-center {
            text-align: center;
            padding: 0 10px;
        }
        .kop-instansi {
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #334155;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .kop-madrasah {
            font-size: 12pt;
            font-weight: 800;
            text-transform: uppercase;
            color: #0f172a;
            margin-bottom: 3px;
        }
        .kop-alamat {
            font-size: 7.5pt;
            color: #475569;
            margin-bottom: 2px;
        }
        .kop-npsn {
            font-size: 7pt;
            color: #64748b;
            font-family: monospace;
        }
        .kop-divider-thick {
            border-top: 2px solid #0f172a;
            margin-top: 6px;
        }
        .kop-divider-thin {
            border-top: 1px solid #64748b;
            margin-top: 2px;
            margin-bottom: 12px;
        }

        /* Document Title */
        .doc-header {
            text-align: center;
            margin-bottom: 12px;
        }
        .doc-title {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            color: #0f172a;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
        }
        .doc-subtitle {
            font-size: 8.5pt;
            color: #475569;
        }

        /* KPI Summary Table */
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border: 1px solid #cbd5e1;
        }
        .kpi-table td {
            text-align: center;
            padding: 6px 4px;
            border-right: 1px solid #cbd5e1;
            background-color: #f8fafc;
        }
        .kpi-table td:last-child {
            border-right: none;
        }
        .kpi-label {
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            display: block;
            margin-bottom: 2px;
        }
        .kpi-value {
            font-size: 11pt;
            font-weight: bold;
            font-family: monospace;
            color: #0f172a;
        }
        .kpi-hadir { color: #166534; }
        .kpi-izin { color: #b45309; }
        .kpi-sakit { color: #0369a1; }
        .kpi-alpa { color: #be123c; }

        /* Data Attendance Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 15px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
        }
        .data-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #334155;
            text-align: center;
            font-size: 8pt;
        }
        .data-table tr.zebra {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-mono { font-family: monospace; }
        .font-bold { font-weight: bold; }
        .badge-hadir { color: #166534; font-weight: bold; }
        .badge-izin { color: #b45309; }
        .badge-sakit { color: #0369a1; }
        .badge-alpa { color: #be123c; font-weight: bold; }

        /* Signature Table */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            page-break-inside: avoid;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 15px;
            font-size: 8.5pt;
        }
        .signature-space {
            height: 55px;
        }
        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            font-size: 9pt;
            color: #0f172a;
        }
        .signature-nip {
            font-size: 7.5pt;
            color: #64748b;
            font-family: monospace;
            margin-top: 2px;
        }

        /* Footer */
        .footer-note {
            margin-top: 20px;
            font-size: 7pt;
            color: #94a3b8;
            border-top: 1px dashed #cbd5e1;
            padding-top: 4px;
        }
    </style>
</head>
<body>

    <!-- Kop Surat Resmi -->
    <table class="kop-table">
        <tr>
            <td style="width: 65px;">
                <div class="kop-logo-box">NU</div>
            </td>
            <td class="kop-text-center">
                <div class="kop-instansi">Lembaga Pendidikan Ma'arif Nahdlatul Ulama Jawa Barat</div>
                <div class="kop-madrasah">Madrasah Aliyah Ma'arif Cilageni (Setingkat SMA)</div>
                <div class="kop-alamat">Alamat: Jl. Raya Cilageni No. 23 Kadungora &bull; Kab. Garut 44153 &bull; Telp: (0262) 512345</div>
                <div class="kop-npsn">NPSN: 20277890 &bull; NSM: 121232050012 &bull; Akreditasi "A" (Unggul)</div>
            </td>
            <td style="width: 65px; text-align: right;">
                <div class="kop-emblem-box">M</div>
            </td>
        </tr>
    </table>

    <div class="kop-divider-thick"></div>
    <div class="kop-divider-thin"></div>

    <!-- Judul Dokumen & Periode -->
    <div class="doc-header">
        <div class="doc-title">Laporan Rekapitulasi Presensi Kehadiran Siswa</div>
        <div class="doc-subtitle">
            Periode: <strong>{{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }}</strong> s/d <strong>{{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</strong>
            @if($selectedClass)
                &bull; Kelas: <strong>{{ $selectedClass->name }}</strong>
            @endif
        </div>
    </div>

    <!-- KPI Summary Box -->
    <table class="kpi-table">
        <tr>
            <td style="width: 20%;">
                <span class="kpi-label">Total Jam</span>
                <span class="kpi-value">{{ $stats['total_records'] }}</span>
            </td>
            <td style="width: 20%;">
                <span class="kpi-label kpi-hadir">Hadir</span>
                <span class="kpi-value kpi-hadir">{{ $stats['total_hadir'] }}</span>
            </td>
            <td style="width: 20%;">
                <span class="kpi-label kpi-izin">Izin</span>
                <span class="kpi-value kpi-izin">{{ $stats['total_izin'] }}</span>
            </td>
            <td style="width: 20%;">
                <span class="kpi-label kpi-sakit">Sakit</span>
                <span class="kpi-value kpi-sakit">{{ $stats['total_sakit'] }}</span>
            </td>
            <td style="width: 20%;">
                <span class="kpi-label kpi-alpa">Alpa</span>
                <span class="kpi-value kpi-alpa">{{ $stats['total_alpa'] }}</span>
            </td>
        </tr>
    </table>

    <!-- Data Presensi Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 16%;">NISN</th>
                <th style="width: 33%; text-align: left;">Nama Siswa</th>
                <th style="width: 10%;">Kelas</th>
                <th style="width: 6%; color: #166534;">H</th>
                <th style="width: 6%; color: #b45309;">I</th>
                <th style="width: 6%; color: #0369a1;">S</th>
                <th style="width: 6%; color: #be123c;">A</th>
                <th style="width: 8%;">Total</th>
                <th style="width: 10%; text-align: right;">% Hadir</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $idx => $r)
                <tr class="{{ $idx % 2 === 1 ? 'zebra' : '' }}">
                    <td class="text-center font-mono">{{ $idx + 1 }}</td>
                    <td class="text-center font-mono font-bold">{{ $r['nisn'] }}</td>
                    <td class="text-left font-bold">{{ $r['name'] }}</td>
                    <td class="text-center">{{ $r['class_name'] }}</td>
                    <td class="text-center font-mono badge-hadir">{{ $r['hadir'] }}</td>
                    <td class="text-center font-mono badge-izin">{{ $r['izin'] }}</td>
                    <td class="text-center font-mono badge-sakit">{{ $r['sakit'] }}</td>
                    <td class="text-center font-mono badge-alpa">{{ $r['alpa'] }}</td>
                    <td class="text-center font-mono">{{ $r['total'] }}</td>
                    <td class="text-right font-mono font-bold">{{ $r['persentase'] }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 15px; color: #94a3b8;">
                        Tidak ada rekaman presensi pada rentang periode yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Lembar Pengesahan / Tanda Tangan -->
    <table class="signature-table">
        <tr>
            <td>
                <div>Mengetahui / Memeriksa,</div>
                <div style="font-weight: bold; color: #0f172a; margin-top: 2px;">{{ $signerTitle }}</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $signerName }}</div>
                @if($signerNip)
                    <div class="signature-nip">NIP. {{ $signerNip }}</div>
                @endif
            </td>
            <td>
                <div>{{ $signatureCity }}, {{ \Carbon\Carbon::parse($signatureDate)->translatedFormat('d F Y') }}</div>
                <div style="font-weight: bold; color: #0f172a; margin-top: 2px;">{{ $headmasterTitle }}</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $headmasterName }}</div>
                @if($headmasterNip)
                    <div class="signature-nip">NIP. {{ $headmasterNip }}</div>
                @endif
            </td>
        </tr>
    </table>

    <!-- Footer Catatan Otentikasi -->
    <div class="footer-note">
        Dokumen ini dihasilkan secara otomatis oleh Sistem Absensi & Kehadiran Digital MA Ma'arif Cilageni pada {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB.
    </div>

</body>
</html>
