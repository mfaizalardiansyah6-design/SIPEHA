<x-app-layout :title="$petugas ? 'Edit Petugas' : 'Tambah Petugas'">
    <x-page-header :title="$petugas ? 'Edit Petugas' : 'Tambah Petugas'"
                   :description="$petugas ? 'Perbarui akun petugas' : 'Buat akun petugas baru untuk memantau layanan'">
        <x-slot:actions>
            <a href="{{ route('petugas.index') }}" class="btn-secondary">
                <x-icon name="chevron-left" class="w-4 h-4" />
                Kembali
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="card card-padding max-w-3xl">
        <form method="POST" action="{{ $petugas ? route('petugas.update', $petugas) : route('petugas.store') }}"
              class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            @csrf
            @if ($petugas)
                @method('PUT')
            @endif

            <div>
                <x-input-label for="name" value="Nama Lengkap" />
                <input type="text" id="name" name="name" value="{{ old('name', $petugas?->name) }}" class="form-input" required>
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="nip" value="NIP" />
                <input type="text" id="nip" name="nip" value="{{ old('nip', $petugas?->nip) }}" class="form-input" required>
                <x-input-error :messages="$errors->get('nip')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="email" value="Email" />
                <input type="email" id="email" name="email" value="{{ old('email', $petugas?->email) }}" class="form-input" required>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="jabatan" value="Jabatan" />
                <input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan', $petugas?->jabatan) }}" class="form-input" placeholder="contoh: Sipir, Bimbingan Kemasyarakatan">
                <x-input-error :messages="$errors->get('jabatan')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="role" value="Peran" />
                <select id="role" name="role" class="form-input" required>
                    @foreach (App\Enums\UserRole::cases() as $option)
                        <option value="{{ $option->value }}" @selected(old('role', $petugas?->role?->value) === $option->value)>
                            {{ $option->label() }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('role')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" :value="$petugas ? 'Password Baru (opsional)' : 'Password'" />
                <input type="password" id="password" name="password" class="form-input" @if (!$petugas) required @endif autocomplete="new-password">
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" value="Konfirmasi Password" />
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" autocomplete="new-password">
            </div>

            <div class="sm:col-span-2 flex items-center justify-end gap-3 border-t border-line pt-5">
                <a href="{{ route('petugas.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">
                    <x-icon name="check" class="w-4 h-4" />
                    {{ $petugas ? 'Simpan Perubahan' : 'Buat Akun' }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
