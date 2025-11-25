<x-app-layout title="Manajemen User">
    <div class="card mb-3">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title">Daftar User</h3>
            <div class="ms-auto d-print-none">
                <a href="{{ route('users.create') }}" class="btn btn-primary">Tambah User</a>
            </div>
        </div>
        @if(session('success'))
            <div class="alert alert-success m-3" role="alert">{{ session('success') }}</div>
        @endif
        <div class="table-responsive">
            <table class="table table-vcenter table-striped table-hover card-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="w-1">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-xs me-2 rounded" style="background-image: url({{ asset('tabler/img/avatars/avatar-placeholder.png') }})"></span>
                                    {{ $user->name }}
                                </div>
                            </td>
                            <td class="text-secondary">{{ $user->email }}</td>
                            <td class="text-secondary">{{ $user->username }}</td>
                            <td>
                                @php
                                    $roleBadges = [
                                        'admin' => 'text-primary bg-primary-lt',
                                        'operator' => 'text-secondary bg-secondary-lt',
                                    ];
                                @endphp
                                @foreach($user->roles as $role)
                                    <span class="badge {{ $roleBadges[$role->name] ?? 'bg-muted' }} fw-bold text-uppercase">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success text-white fw-bold">Aktif</span>
                                @else
                                    <span class="badge bg-danger text-white fw-bold">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-ghost-secondary" title="Edit">
                                        Edit
                                    </a>
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-ghost-danger" title="Delete">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="empty">
                                    <div class="empty-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                                    </div>
                                    <p class="empty-title">Belum ada user</p>
                                    <p class="empty-subtitle text-secondary">Tambahkan user baru dengan klik tombol di kanan atas</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="card-footer d-flex align-items-center">
            <p class="m-0 text-secondary">Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} entries</p>
            <ul class="pagination m-0 ms-auto">
                {{ $users->links() }}
            </ul>
        </div>
        @endif
    </div>
</x-app-layout>