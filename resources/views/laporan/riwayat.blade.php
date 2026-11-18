@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>Riwayat Perubahan Sistem (Baca-Saja)</h2>
</div>
<div class="table-container">
    <table>
        <thead><tr><th>Waktu</th><th>Pengguna</th><th>Aktivitas</th><th>Objek</th></tr></thead>
        <tbody>
            @forelse($logs as $l)
            <tr>
                <td>{{ $l->created_at?->format('d/m/Y H:i:s') }}</td>
                <td>{{ $l->causer?->name ?? 'Sistem' }}</td>
                <td>{{ $l->description }}</td>
                <td>{{ class_basename($l->subject_type ?? '-') }} #{{ $l->subject_id }}</td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center;">Belum ada catatan aktivitas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
