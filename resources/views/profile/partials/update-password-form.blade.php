<section>
    <p class="text-muted">Gunakan kata sandi yang kuat untuk keamanan akun.</p>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="mb-3">
            <label class="form-label" for="update_password_current_password">Password Saat Ini</label>
            <input id="update_password_current_password" name="current_password" type="password" class="form-control" autocomplete="current-password">
            @if($errors->updatePassword?->get('current_password'))
                <div class="text-danger small">{{ $errors->updatePassword->first('current_password') }}</div>
            @endif
        </div>

        <div class="mb-3">
            <label class="form-label" for="update_password_password">Password Baru</label>
            <input id="update_password_password" name="password" type="password" class="form-control" autocomplete="new-password">
            @if($errors->updatePassword?->get('password'))
                <div class="text-danger small">{{ $errors->updatePassword->first('password') }}</div>
            @endif
        </div>

        <div class="mb-3">
            <label class="form-label" for="update_password_password_confirmation">Konfirmasi Password</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password">
            @if($errors->updatePassword?->get('password_confirmation'))
                <div class="text-danger small">{{ $errors->updatePassword->first('password_confirmation') }}</div>
            @endif
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Simpan</button>
            @if (session('status') === 'password-updated')
                <span class="text-muted ms-2">Tersimpan.</span>
            @endif
        </div>
    </form>
</section>
