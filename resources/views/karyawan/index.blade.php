@extends('layouts.app')
@section('content')
<div class="page-header">
    <div>
        <h2>Data Karyawan & Sopir</h2>
        <p style="color:var(--text-light); font-size:0.85rem;">Kelola personel cabang, masa berlaku SIM, dan akun akses Portal Sopir.</p>
    </div>
    <a href="{{ route('karyawan.create') }}" class="btn btn-sm">+ Tambah Karyawan</a>
</div>

<form action="{{ route('karyawan.index') }}" method="GET" class="filter-bar">
    <div class="filter-group">
        <input type="text" name="q" value="{{ request('q') }}" class="filter-input" placeholder="Cari nama atau kode...">
        <select name="jabatan" class="filter-input">
            <option value="">Semua Jabatan</option>
            @foreach(['sopir', 'kernet', 'mekanik', 'staf_gudang', 'staf_admin'] as $j)
                <option value="{{ $j }}" @selected(request('jabatan') === $j)>{{ ucfirst(str_replace('_', ' ', $j)) }}</option>
            @endforeach
        </select>
        @if(Auth::user()->hasAnyRole(['admin_pusat', 'keuangan']))
        <select name="cabang_id" class="filter-input">
            <option value="">Semua Cabang</option>
            @foreach($cabangs as $c)
                <option value="{{ $c->id }}" @selected(request('cabang_id') == $c->id)>{{ $c->nama }}</option>
            @endforeach
        </select>
        @endif
        <button type="submit" class="btn btn-sm">Filter</button>
        @if(request()->anyFilled(['q', 'jabatan', 'cabang_id']))
            <a href="{{ route('karyawan.index') }}" class="btn-secondary" style="padding:0.45rem 0.75rem; font-size:0.82rem;">Reset</a>
        @endif
    </div>
    <small style="color:var(--text-light); font-weight:600;">Total: {{ $karyawans->count() }} data</small>
</form>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama & Kontak</th>
                <th>Jabatan</th>
                <th>Lisensi SIM</th>
                <th>Cabang</th>
                <th>Status</th>
                <th>Akun Portal Sopir</th>
                <th style="text-align:right;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($karyawans as $k)
            <tr>
                <td><span class="code-pill">{{ $k->kode }}</span></td>
                <td>
                    <strong>{{ $k->nama }}</strong>
                    @if($k->no_hp)<br><small style="color:var(--text-light);">{{ $k->no_hp }}</small>@endif
                </td>
                <td><span class="role-pill">{{ ucfirst(str_replace('_', ' ', $k->jabatan)) }}</span></td>
                <td>
                    @if($k->jenis_sim)
                        <strong>SIM {{ $k->jenis_sim }}</strong><br>
                        <small style="color:var(--text-light);">s/d {{ $k->sim_berlaku_sampai?->format('d/m/Y') ?? '-' }}</small>
                    @else
                        <span style="color:var(--text-light);">-</span>
                    @endif
                </td>
                <td>{{ $k->cabang ? $k->cabang->nama : 'Pusat' }}</td>
                <td><span class="badge {{ $k->aktif ? 'active' : 'inactive' }}">{{ $k->aktif ? 'Aktif' : 'Nonaktif' }}</span></td>
                <td>
                    @if($k->jabatan === 'sopir' && $k->aktif)
                        @if($k->user)
                            <div class="action-group">
                                <span class="code-pill" style="background:#F0FDF4; color:#166534; border-color:#BBF7D0;">@{{ $k->user->username }}</span>
                                <form action="{{ route('karyawan.reset-password', $k) }}" method="POST" style="display:inline;" onsubmit="return confirm('Reset kata sandi akun {{ $k->user->username }}?')">
                                    @csrf
                                    <button type="submit" class="btn-action btn-warn">Reset Sandi</button>
                                </form>
                            </div>
                        @else
                            <form action="{{ route('karyawan.akun', $k) }}" method="POST" class="inline-account-form">
                                @csrf
                                <input type="text" name="username" class="inline-account-input" placeholder="username..." required>
                                <button type="submit" class="btn-action btn-success-soft">+ Buat Akun</button>
                            </form>
                        @endif
                    @else
                        <span style="color:var(--text-light); font-size:0.8rem;">Tidak diperlukan</span>
                    @endif
                </td>
                <td style="text-align:right;">
                    <div class="action-group">
                        <a href="{{ route('karyawan.edit', $k) }}" class="btn-action btn-edit">Edit</a>
                        <form action="{{ route('karyawan.destroy', $k) }}" method="POST" style="display:inline;" onsubmit="return confirm('Nonaktifkan/hapus karyawan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-delete">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align:center; color:var(--text-light); padding:2rem;">Tidak ada data karyawan yang sesuai filter.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
