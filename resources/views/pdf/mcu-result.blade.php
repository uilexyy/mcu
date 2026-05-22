<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Hasil Medical Check Up</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #1a5276; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { color: #1a5276; margin: 5px 0; font-size: 18pt; }
        .header h2 { color: #2e86c1; margin: 5px 0; font-size: 14pt; }
        .header p { margin: 2px 0; font-size: 10pt; color: #666; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 4px 8px; border: 1px solid #ddd; }
        .info-table td.label { width: 30%; background: #f8f9fa; font-weight: bold; }
        .section-title { background: #1a5276; color: white; padding: 6px 10px; font-size: 12pt; margin: 15px 0 5px 0; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .data-table th { background: #2e86c1; color: white; padding: 6px 8px; text-align: left; font-size: 10pt; }
        .data-table td { padding: 5px 8px; border: 1px solid #ddd; font-size: 10pt; }
        .data-table tr:nth-child(even) { background: #f8f9fa; }
        .footer { margin-top: 40px; text-align: center; font-size: 9pt; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
        .ttd { margin-top: 30px; }
        .ttd table { width: 100%; }
        .ttd td { text-align: center; padding-top: 50px; font-size: 10pt; }
        .normal { color: #27ae60; font-weight: bold; }
        .tinggi { color: #e74c3c; font-weight: bold; }
        .rendah { color: #e67e22; font-weight: bold; }
        .photo-container { text-align: center; margin: 10px 0; }
        .photo-container img { max-width: 400px; max-height: 300px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="header">
        <h1>RUMAH SAKIT JUWITA</h1>
        <p>Jl. Kesehatan No. 123, Jakarta</p>
        <p>Telp: (021) 1234567 | Email: info@rsjuwita.com</p>
        <h2>HASIL MEDICAL CHECK UP</h2>
        <p>No. Registrasi: MCU-{{ $registration->id }}/{{ $registration->created_at->format('Y') }}</p>
        <p>Tanggal: {{ $registration->tanggal_jadwal ? $registration->tanggal_jadwal->format('d F Y') : '-' }}</p>
    </div>

    <h3 class="section-title">Data Karyawan</h3>
    <table class="info-table">
        <tr>
            <td class="label">Nama Lengkap</td>
            <td>{{ $registration->user->name }}</td>
        </tr>
        <tr>
            <td class="label">NIK / NIP</td>
            <td>{{ $registration->user->nip ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Departemen</td>
            <td>{{ $registration->user->departemen ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Lahir</td>
            <td>{{ $registration->user->tanggal_lahir ? $registration->user->tanggal_lahir->format('d F Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Jenis Kelamin</td>
            <td>{{ $registration->user->jenis_kelamin == 'L' ? 'Laki-laki' : ($registration->user->jenis_kelamin == 'P' ? 'Perempuan' : '-') }}</td>
        </tr>
        <tr>
            <td class="label">Paket MCU</td>
            <td>{{ $registration->package->nama_paket }}</td>
        </tr>
    </table>

    @if($registration->physicalExam)
    <h3 class="section-title">Pemeriksaan Fisik</h3>
    <table class="data-table">
        <tr>
            <th style="width:40%">Parameter</th>
            <th style="width:60%">Hasil</th>
        </tr>
        <tr><td>Tekanan Darah</td><td>{{ $registration->physicalExam->tekanan_darah ?? '-' }}</td></tr>
        <tr><td>Berat Badan</td><td>{{ $registration->physicalExam->berat_badan ? $registration->physicalExam->berat_badan . ' kg' : '-' }}</td></tr>
        <tr><td>Tinggi Badan</td><td>{{ $registration->physicalExam->tinggi_badan ? $registration->physicalExam->tinggi_badan . ' cm' : '-' }}</td></tr>
        <tr><td>IMT</td><td>{{ $registration->physicalExam->imt ?? '-' }}</td></tr>
        <tr><td>Anamnesis</td><td>{{ $registration->physicalExam->anamnesis ?? '-' }}</td></tr>
        <tr><td colspan="2"><strong>Catatan Dokter:</strong> {{ $registration->physicalExam->catatan ?? '-' }}</td></tr>
    </table>
    @endif

    @if($registration->labResults->count() > 0)
    <h3 class="section-title">Hasil Laboratorium</h3>
    <table class="data-table">
        <tr>
            <th style="width:35%">Pemeriksaan</th>
            <th style="width:15%">Hasil</th>
            <th style="width:15%">Satuan</th>
            <th style="width:20%">Nilai Normal</th>
            <th style="width:15%">Keterangan</th>
        </tr>
        @foreach($registration->labResults as $lab)
        <tr>
            <td>{{ $lab->item->nama_pemeriksaan ?? '-' }}</td>
            <td>{{ $lab->nilai ?? '-' }}</td>
            <td>{{ $lab->item->satuan ?? '-' }}</td>
            <td>{{ $lab->item->nilai_normal ?? '-' }}</td>
            <td class="{{ strtolower($lab->keterangan) }}">{{ $lab->keterangan ?? '-' }}</td>
        </tr>
        @endforeach
    </table>
    <p style="font-size:9pt;">Petugas Lab: {{ $registration->labResults->first()->labUser->name ?? '-' }}</p>
    @endif

    @if($registration->radiologyResult)
    <h3 class="section-title">Hasil Radiologi</h3>
    <table class="data-table">
        <tr><th style="width:30%">Interpretasi</th><td>{{ $registration->radiologyResult->interpretasi ?? '-' }}</td></tr>
    </table>
    @if($registration->radiologyResult->file_path)
    <div class="photo-container">
        <img src="{{ storage_path('app/public/' . $registration->radiologyResult->file_path) }}" alt="Foto Rontgen">
        <p style="font-size:9pt; color:#666;">Foto Rontgen Thorax</p>
    </div>
    @endif
    <p style="font-size:9pt;">Petugas Radiologi: {{ $registration->radiologyResult->radioUser->name ?? '-' }}</p>
    @endif

    <div class="ttd">
        <table>
            <tr>
                <td style="width:50%">
                    <p>Dokter Pemeriksa</p>
                    @if(isset($registration->physicalExam->doctor) && $registration->physicalExam->doctor->signature)
                        <br>
                        <img src="{{ storage_path('app/public/' . $registration->physicalExam->doctor->signature) }}" style="max-height:60px;">
                        <br>
                    @else
                        <br><br>
                    @endif
                    <p>({{ $registration->physicalExam->doctor->nama_lengkap ?? $registration->physicalExam->doctor->name ?? '____________________' }})</p>
                    <p style="font-size:9pt; color:#666;">{{ $registration->physicalExam->doctor->sip ?? '-' }}</p>
                </td>
                <td style="width:50%">
                    <p>Jakarta, {{ $result->generated_at ? $result->generated_at->format('d F Y') : date('d F Y') }}</p>
                    <br><br>
                    <p>RUMAH SAKIT JUWITA</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Dokumen ini adalah hasil resmi Medical Check Up RS Juwita</p>
        <p>Dicetak pada: {{ date('d F Y H:i:s') }}</p>
    </div>
</body>
</html>
