@extends('layouts.app')
@section('content')
<div class="page-header">
    <h2>Pengumuman & Berita Internal</h2>
</div>

<div class="table-container" style="padding:1.25rem; margin-bottom:1.5rem;">
    <h3 style="margin-bottom:0.75rem;">Buat Pengumuman Baru</h3>
    <form action="{{ route('pengumuman.store') }}" method="POST">
        @csrf
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:0.75rem; margin-bottom:0.75rem;">
            <div>
                <label>Judul</label>
                <input type="text" name="judul" class="form-input" required>
            </div>
            <div>
                <label>Kategori</label>
                <select name="kategori" class="form-input" required>
                    <option value="info">Info</option>
                    <option value="keselamatan">Keselamatan</option>
                    <option value="kebijakan">Kebijakan</option>
                    <option value="darurat">Darurat (Disematkan)</option>
                </select>
            </div>
            @if(Auth::user()->hasRole('admin_pusat'))
            <div>
                <label>Target Cabang</label>
                <select name="cabang_id" class="form-input">
                    <option value="">Semua Cabang</option>
                    @foreach($cabangs as $c)
                        <option value="{{ $c->id }}">{{ $c->nama }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div>
                <label>Tanggal Terbit</label>
                <input type="date" name="terbit_pada" class="form-input" value="{{ now()->format('Y-m-d') }}" required>
            </div>
            <div>
                <label>Kedaluwarsa (Opsional)</label>
                <input type="date" name="kedaluwarsa_pada" class="form-input">
            </div>
        </div>
        <div class="form-group">
            <label>Isi Pengumuman</label>
            <textarea name="isi" class="form-input" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-sm" style="width:auto;">Terbitkan Pengumuman</button>
    </form>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Kategori</th>
                <th>Judul</th>
                <th>Cabang</th>
                <th>Terbit</th>
                <th>Dibaca</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pengumumans as $p)
            <tr>
                <td><span class="badge {{ $p->kategori === 'darurat' ? 'inactive' : 'active' }}">{{ strtoupper($p->kategori) }}</span></td>
                <td><strong>{{ $p->judul }}</strong><br><small>{{ \Illuminate\Support\Str::limit($p->isi, 90) }}</small></td>
                <td>{{ $p->cabang?->nama ?? 'Semua Cabang' }}</td>
                <td>{{ $p->terbit_pada?->format('d/m/Y') }}</td>
                <td>{{ $p->pembacas_count }} user</td>
                <td>
                    <form action="{{ route('pengumuman.destroy', $p) }}" method="POST" onsubmit="return confirm('Hapus pengumuman ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background:none;border:none;color:#DC2626;cursor:pointer;">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;">Belum ada pengumuman.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
