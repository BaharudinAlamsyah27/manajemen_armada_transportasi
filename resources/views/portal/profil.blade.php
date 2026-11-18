@extends('layouts.portal')
@section('content')
<h2 style="margin-bottom:1rem;">Profil Saya</h2>
<div class="kartu">
    <div>Nama: <strong>{{ $karyawan->nama }}</strong></div>
    <div>Kode: {{ $karyawan->kode }}</div>
    <div>Cabang: {{ $karyawan->cabang?->nama ?? '-' }}</div>
    <div>No. HP: {{ $karyawan->no_hp ?? '-' }}</div>
    <div>SIM: {{ $karyawan->jenis_sim ?? '-' }} · {{ \App\Models\Pesanan::samarkan($karyawan->no_sim) }}</div>
    <div>Berlaku sampai: {{ $karyawan->sim_berlaku_sampai?->format('d/m/Y') ?? '-' }}</div>
    <p style="color:var(--text-light); margin-top:0.5rem;">Data diri hanya dapat diubah oleh admin cabang.</p>
</div>
<div class="kartu">
    <h3>Rekap Kehadiran Bulan Ini</h3>
    <div>Hadir: <strong>{{ $rekapAbsensi->get('hadir', 0) }} hari</strong> · Izin: {{ $rekapAbsensi->get('izin', 0) }} · Sakit: {{ $rekapAbsensi->get('sakit', 0) }} · Alpa: {{ $rekapAbsensi->get('alpa', 0) }}</div>
</div>
<a href="{{ route('password.ganti') }}" class="btn-besar btn-biru">Ganti Kata Sandi</a>
@endsection
