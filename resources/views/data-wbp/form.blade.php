<x-app-layout :title="$wbp ? 'Edit Data WBP' : 'Tambah Data WBP'">
    <x-page-header :title="$wbp ? 'Edit Data WBP' : 'Tambah Data WBP'"
                   :description="$wbp ? 'Perbarui informasi warga binaan' : 'Catat data warga binaan baru ke dalam sistem'">
        <x-slot:actions>
            <a href="{{ route('wbp.index') }}" class="btn-secondary">
                <x-icon name="chevron-left" class="w-4 h-4" />
                Kembali
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="card card-padding max-w-3xl">
        <form method="POST"
              action="{{ $wbp ? route('wbp.update', $wbp) : route('wbp.store') }}"
              enctype="multipart/form-data"
              class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            @csrf
            @if ($wbp)
                @method('PUT')
            @endif

            <div>
                <x-input-label for="nama" value="Nama Lengkap" />
                <input type="text" id="nama" name="nama" value="{{ old('nama', $wbp?->nama) }}" class="form-input" required>
                <x-input-error :messages="$errors->get('nama')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="nik" value="NIK (16 Digit)" />
                <input type="text" id="nik" name="nik" value="{{ old('nik', $wbp?->nik) }}" class="form-input" maxlength="16" required>
                <x-input-error :messages="$errors->get('nik')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="no_register" value="Nomor Register" />
                <input type="text" id="no_register" name="no_register" value="{{ old('no_register', $wbp?->no_register) }}" class="form-input" required>
                <x-input-error :messages="$errors->get('no_register')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="blok_kamar" value="Blok Kamar" />
                <input type="text" id="blok_kamar" name="blok_kamar" value="{{ old('blok_kamar', $wbp?->blok_kamar) }}" class="form-input" placeholder="contoh: A-12" required>
                <x-input-error :messages="$errors->get('blok_kamar')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="no_hp" value="No. HP (opsional)" />
                <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp', $wbp?->no_hp) }}" class="form-input" placeholder="contoh: 081234567890" maxlength="20">
                <x-input-error :messages="$errors->get('no_hp')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="agama" value="Agama" />
                <select id="agama" name="agama" class="form-input" required>
                    <option value="">Pilih agama</option>
                    @foreach ($agamaOptions as $option)
                        <option value="{{ $option }}" @selected(old('agama', $wbp?->agama) === $option)>{{ $option }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('agama')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="jenis_kelamin" value="Jenis Kelamin (opsional)" />
                <select id="jenis_kelamin" name="jenis_kelamin" class="form-input">
                    <option value="">Pilih jenis kelamin</option>
                    <option value="Laki-laki" @selected(old('jenis_kelamin', $wbp?->jenis_kelamin) === 'Laki-laki')>Laki-laki</option>
                    <option value="Perempuan" @selected(old('jenis_kelamin', $wbp?->jenis_kelamin) === 'Perempuan')>Perempuan</option>
                </select>
                <x-input-error :messages="$errors->get('jenis_kelamin')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="status" value="Status" />
                <select id="status" name="status" class="form-input" required>
                    <option value="">Pilih status</option>
                    @foreach ($statuses as $option)
                        <option value="{{ $option->value }}" @selected(old('status', $wbp?->status?->value) === $option->value)>
                            {{ $option->label() }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('status')" class="mt-2" />
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="foto" value="Foto (opsional)" />
                <input type="file" id="foto" name="foto" accept="image/*" class="form-input">
                <x-input-error :messages="$errors->get('foto')" class="mt-2" />
                @if ($wbp?->foto_url)
                    <p class="mt-2 text-xs text-muted">Foto saat ini: {{ basename($wbp->foto_url) }}</p>
                @endif
            </div>

            <div class="sm:col-span-2 flex items-center justify-end gap-3 border-t border-line pt-5">
                <a href="{{ route('wbp.index') }}" class="btn-secondary">Batal</a>
                <button type="submit" class="btn-primary">
                    <x-icon name="check" class="w-4 h-4" />
                    {{ $wbp ? 'Simpan Perubahan' : 'Simpan Data' }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
