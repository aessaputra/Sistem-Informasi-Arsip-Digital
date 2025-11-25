<section>
    <p class="text-muted">Menghapus akun akan menghapus seluruh data dan sumber daya terkait secara permanen.</p>

    <form method="post" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun ini secara permanen?')">
        @csrf
        @method('delete')
        <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input id="password" name="password" type="password" class="form-control" placeholder="Password">
            @if($errors->userDeletion?->get('password'))
                <div class="text-danger small">{{ $errors->userDeletion->first('password') }}</div>
            @endif
        </div>
        <div class="mt-3">
            <button type="submit" class="btn btn-danger">Hapus Akun</button>
        </div>
    </form>
</section>
