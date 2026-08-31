<x-app-layout title="Pengaturan Akun">
    <x-page-header title="Pengaturan Akun" description="Kelola tampilan, profil, dan foto profil Anda" />

    <div x-data="tabs('tampilan')" class="grid grid-cols-1 gap-6 xl:grid-cols-4">
        <div class="card card-padding h-fit">
            <nav class="flex xl:flex-col gap-1 overflow-x-auto">
                @php
                    $tabs = [
                        'tampilan' => ['label' => 'Tampilan', 'icon' => 'brush'],
                        'profil' => ['label' => 'Edit Profil', 'icon' => 'user'],
                        'foto' => ['label' => 'Foto Profil', 'icon' => 'photo'],
                    ];
                @endphp

                @foreach ($tabs as $key => $tab)
                    <button type="button"
                            @click="activate('{{ $key }}')"
                            class="w-full flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold whitespace-nowrap transition-colors"
                            :class="isActive('{{ $key }}') ? 'bg-primary-soft text-primary' : 'text-body hover:bg-canvas'">
                        <x-icon :name="$tab['icon']" class="w-5 h-5" />
                        {{ $tab['label'] }}
                    </button>
                @endforeach
            </nav>
        </div>

        <div class="xl:col-span-3 space-y-6">
            <div class="card card-padding" x-show="isActive('tampilan')">
                <h2 class="mb-1">Tampilan</h2>
                <p class="text-sm text-muted mb-5">Sesuaikan tampilan aplikasi untuk akun Anda</p>

                <form method="POST" action="{{ route('pengaturan-saya.update-appearance') }}"
                      class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="theme_color" value="Warna Tema" />
                        <select id="theme_color" name="theme_color" class="form-input">
                            @foreach (['blue' => 'Biru', 'teal' => 'Teal', 'indigo' => 'Indigo', 'slate' => 'Slate'] as $value => $label)
                                <option value="{{ $value }}" @selected($user->theme_color === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="mode" value="Mode" />
                        <select id="mode" name="mode" class="form-input">
                            @foreach (['light' => 'Terang', 'dark' => 'Gelap'] as $value => $label)
                                <option value="{{ $value }}" @selected($user->mode === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1.5 text-xs text-muted">Kosongkan/membiarkan default akan mengikuti pengaturan instansi.</p>
                    </div>

                    <div class="sm:col-span-2 flex justify-end border-t border-line pt-5">
                        <button type="submit" class="btn-primary">
                            <x-icon name="check" class="w-4 h-4" />
                            Simpan Tampilan
                        </button>
                    </div>
                </form>
            </div>

            <div class="card card-padding" x-show="isActive('profil')">
                <h2 class="mb-1">Edit Profil</h2>
                <p class="text-sm text-muted mb-5">Perbarui data pribadi akun Anda</p>

                <form method="POST" action="{{ route('pengaturan-saya.update-profile') }}"
                      class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" value="Nama Lengkap" />
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-input" required>
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="nip" value="NIP" />
                        <input type="text" id="nip" name="nip" value="{{ old('nip', $user->nip) }}" class="form-input" required>
                        <x-input-error :messages="$errors->get('nip')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Email" />
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-input" required>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="jabatan" value="Jabatan" />
                        <input type="text" id="jabatan" name="jabatan" value="{{ old('jabatan', $user->jabatan) }}" class="form-input" placeholder="contoh: Sipir, Bimbingan Kemasyarakatan">
                        <x-input-error :messages="$errors->get('jabatan')" class="mt-2" />
                    </div>

                    <div class="sm:col-span-2 flex justify-end border-t border-line pt-5">
                        <button type="submit" class="btn-primary">
                            <x-icon name="check" class="w-4 h-4" />
                            Simpan Profil
                        </button>
                    </div>
                </form>
            </div>

            <div class="card card-padding" x-show="isActive('foto')">
                <h2 class="mb-1">Foto Profil</h2>
                <p class="text-sm text-muted mb-5">Unggah foto untuk ditampilkan di menu pengguna</p>

                <form method="POST" action="{{ route('pengaturan-saya.update-photo') }}"
                      enctype="multipart/form-data"
                      class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center gap-5">
                        <div class="size-20 rounded-full bg-ink/5 text-ink text-2xl font-semibold flex items-center justify-center shrink-0 overflow-hidden">
                            @if ($user->profilePhotoUrl)
                                <img src="{{ $user->profilePhotoUrl }}" alt="{{ $user->name }}" class="size-20 object-cover">
                            @else
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-ink">{{ $user->name }}</p>
                            <p class="text-xs text-muted mt-0.5">
                                @if ($user->profilePhotoUrl)
                                    Foto sudah terpasang. Unggah yang baru untuk menggantinya.
                                @else
                                    Belum ada foto profil.
                                @endif
                            </p>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="photo" value="Pilih Foto" />
                        <input type="file" id="photo" name="photo" accept="image/*" class="form-input" required>
                        <x-input-error :messages="$errors->get('photo')" class="mt-2" />
                        <p class="mt-1.5 text-xs text-muted">Format PNG, JPG, atau WebP. Maksimal 2 MB.</p>
                    </div>

                    <div class="sm:col-span-2 flex justify-end border-t border-line pt-5">
                        <button type="submit" class="btn-primary">
                            <x-icon name="upload" class="w-4 h-4" />
                            Unggah Foto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
