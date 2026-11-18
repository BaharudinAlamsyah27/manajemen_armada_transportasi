@extends($layout)
@section('content')
<h2 style="margin-bottom:1rem;">Ganti Kata Sandi</h2>
@if(Auth::user()->wajib_ganti_password)
    <div class="peringatan" style="background:#FEF3C7; color:#92400E; padding:0.75rem; border-radius:0.5rem; margin-bottom:1rem;">
        Anda masih memakai kata sandi awal. Ganti terlebih dahulu sebelum melanjutkan.
    </div>
@endif
<div class="table-container" style="padding:1.25rem; max-width:480px;">
    <form action="{{ route('password.ganti.simpan') }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label>Kata sandi lama</label>
            <input type="password" name="password_lama" class="form-input" required autocomplete="current-password">
        </div>
        <div class="form-group">
            <label>Kata sandi baru (min. 8 karakter)</label>
            <input type="password" name="password" class="form-input" required minlength="8" autocomplete="new-password">
        </div>
        <div class="form-group">
            <label>Ulangi kata sandi baru</label>
            <input type="password" name="password_confirmation" class="form-input" required minlength="8" autocomplete="new-password">
        </div>
        <button type="submit" class="btn">Simpan Kata Sandi</button>
    </form>
</div>
@endsection
