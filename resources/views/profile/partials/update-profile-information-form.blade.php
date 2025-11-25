<section>
    <p class="text-muted">Update informasi profil dan alamat email Anda.</p>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label class="form-label" for="name">Nama</label>
            <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $user->name) }}" required autocomplete="name">
            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="username">Username</label>
            <input id="username" type="text" class="form-control" value="{{ auth()->user()->username }}" disabled>
            <div class="form-text">Username hanya dapat diubah oleh admin melalui Manajemen User.</div>
        </div>

        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')<div class="text-danger small">{{ $message }}</div>@enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-muted">
                        Email Anda belum terverifikasi.
                        <button form="send-verification" class="btn btn-link p-0">Kirim ulang tautan verifikasi</button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="text-success">Tautan verifikasi baru telah dikirim ke email Anda.</p>
                    @endif
                </div>
            @endif
        </div>

        <div class="mb-3">
            <label class="form-label" for="avatar">Avatar</label>
            <input id="avatar" name="avatar" type="file" class="form-control" accept="image/png,image/jpeg">
            @error('avatar')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Simpan</button>
            @if (session('status') === 'profile-updated')
                <span class="text-muted ms-2">Tersimpan.</span>
            @endif
        </div>
    </form>
</section>
