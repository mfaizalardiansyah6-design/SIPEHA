<x-app-layout title="Pengaturan">
    <x-page-header title="Pengaturan" description="Konfigurasi aplikasi SIMHAK WBP" />

    @php
        $groups = [
            'instansi' => ['label' => 'Instansi', 'icon' => 'home'],
            'tampilan' => ['label' => 'Tampilan', 'icon' => 'brush'],
            'sistem' => ['label' => 'Sistem', 'icon' => 'shield-check'],
            'dokumen' => ['label' => 'Dokumen & Upload', 'icon' => 'document'],
            'backup' => ['label' => 'Backup', 'icon' => 'database'],
        ];
    @endphp

    <div x-data="tabs('instansi')" class="grid grid-cols-1 gap-6 xl:grid-cols-4">
        <div class="card card-padding h-fit">
            <nav class="flex xl:flex-col gap-1 overflow-x-auto">
                @foreach ($groups as $key => $group)
                    <button type="button"
                            @click="activate('{{ $key }}')"
                            class="w-full flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-semibold whitespace-nowrap transition-colors"
                            :class="isActive('{{ $key }}') ? 'bg-primary-soft text-primary' : 'text-body hover:bg-canvas'">
                        <x-icon :name="$groups[$key]['icon']" class="w-5 h-5" />
                        {{ $group['label'] }}
                    </button>
                @endforeach
            </nav>

            <div class="border-t border-line mt-4 pt-4">
                <form method="POST" action="{{ route('pengaturan.reset') }}">
                    @csrf
                    <button type="submit" class="btn-secondary w-full" onclick="return confirm('Kembalikan seluruh pengaturan ke nilai default?')">
                        <x-icon name="refresh" class="w-4 h-4" />
                        Reset ke Default
                    </button>
                </form>
            </div>
        </div>

        <div class="xl:col-span-3 space-y-6">
            @foreach ($groups as $key => $group)
                <div class="card card-padding" x-show="isActive('{{ $key }}')">
                    <h2 class="mb-1">{{ $group['label'] }}</h2>
                    <p class="text-sm text-muted mb-5">Atur konfigurasi {{ $group['label'] }} aplikasi</p>

                    <form method="POST"
                          action="{{ route('pengaturan.update', $key) }}"
                          enctype="{{ $key === 'instansi' ? 'multipart/form-data' : 'application/x-www-form-urlencoded' }}"
                          class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        @csrf
                        @method('PUT')

                        @if ($key === 'instansi')
                            <div>
                                <x-input-label for="nama" value="Nama Instansi" />
                                <input type="text" id="nama" name="nama" value="{{ $settings['instansi']['nama'] }}" class="form-input">
                            </div>
                            <div>
                                <x-input-label for="kode" value="Kode Instansi" />
                                <input type="text" id="kode" name="kode" value="{{ $settings['instansi']['kode'] }}" class="form-input">
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label for="alamat" value="Alamat" />
                                <textarea id="alamat" name="alamat" rows="2" class="form-input">{{ $settings['instansi']['alamat'] }}</textarea>
                            </div>
                            <div>
                                <x-input-label for="telepon" value="Telepon" />
                                <input type="text" id="telepon" name="telepon" value="{{ $settings['instansi']['telepon'] }}" class="form-input">
                            </div>
                            <div>
                                <x-input-label for="email" value="Email" />
                                <input type="email" id="email" name="email" value="{{ $settings['instansi']['email'] }}" class="form-input">
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label for="logo" value="Logo Instansi" />
                                <input type="file" id="logo" name="logo" accept="image/*" class="form-input">
                                @if ($logoUrl)
                                    <p class="mt-2 text-xs text-muted">Logo saat ini sudah terpasang.</p>
                                @endif
                            </div>
                        @endif

                        @if ($key === 'tampilan')
                            <div>
                                <x-input-label for="theme_color" value="Warna Tema" />
                                <select id="theme_color" name="theme_color" class="form-input">
                                    @foreach (['blue' => 'Biru', 'teal' => 'Teal', 'indigo' => 'Indigo', 'slate' => 'Slate'] as $value => $label)
                                        <option value="{{ $value }}" @selected($settings['tampilan']['theme_color'] === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="mode" value="Mode" />
                                <select id="mode" name="mode" class="form-input">
                                    @foreach (['light' => 'Terang', 'dark' => 'Gelap'] as $value => $label)
                                        <option value="{{ $value }}" @selected($settings['tampilan']['mode'] === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="bahasa" value="Bahasa" />
                                <select id="bahasa" name="bahasa" class="form-input">
                                    <option value="id" @selected($settings['tampilan']['bahasa'] === 'id')>Indonesia</option>
                                    <option value="en" @selected($settings['tampilan']['bahasa'] === 'en')>English</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label for="zona_waktu" value="Zona Waktu" />
                                <select id="zona_waktu" name="zona_waktu" class="form-input">
                                    <option value="Asia/Jakarta" @selected($settings['tampilan']['zona_waktu'] === 'Asia/Jakarta')>WIB (UTC+7)</option>
                                    <option value="Asia/Makassar" @selected($settings['tampilan']['zona_waktu'] === 'Asia/Makassar')>WITA (UTC+8)</option>
                                    <option value="Asia/Jayapura" @selected($settings['tampilan']['zona_waktu'] === 'Asia/Jayapura')>WIT (UTC+9)</option>
                                </select>
                            </div>
                        @endif

                        @if ($key === 'sistem')
                            <div class="sm:col-span-2 space-y-4">
                                <label class="flex items-center gap-3">
                                    <input type="hidden" name="audit_log" value="0">
                                    <input type="checkbox" name="audit_log" value="1" @checked($settings['sistem']['audit_log']) class="size-4 rounded border-line text-primary focus:ring-primary/20">
                                    <span class="text-sm text-body">Aktifkan pencatatan audit log</span>
                                </label>
                                <label class="flex items-center gap-3">
                                    <input type="hidden" name="auto_lock" value="0">
                                    <input type="checkbox" name="auto_lock" value="1" @checked($settings['sistem']['auto_lock']) class="size-4 rounded border-line text-primary focus:ring-primary/20">
                                    <span class="text-sm text-body">Aktifkan kunci otomatis sesi</span>
                                </label>
                            </div>
                            <div>
                                <x-input-label for="session_timeout" value="Sesi Kedaluwarsa (menit)" />
                                <input type="number" id="session_timeout" name="session_timeout" value="{{ $settings['sistem']['session_timeout'] }}" class="form-input">
                            </div>
                            <div>
                                <x-input-label for="login_attempts" value="Maksimum Percobaan Login" />
                                <input type="number" id="login_attempts" name="login_attempts" value="{{ $settings['sistem']['login_attempts'] }}" class="form-input">
                            </div>
                        @endif

                        @if ($key === 'dokumen')
                            <div>
                                <x-input-label for="max_upload_mb" value="Maksimum Upload (MB)" />
                                <input type="number" id="max_upload_mb" name="max_upload_mb" value="{{ $settings['dokumen']['max_upload_mb'] }}" class="form-input">
                            </div>
                            <div>
                                <x-input-label for="storage_location" value="Lokasi Penyimpanan" />
                                <select id="storage_location" name="storage_location" class="form-input">
                                    @foreach (['local' => 'Lokal', 's3' => 'Cloud (S3)'] as $value => $label)
                                        <option value="{{ $value }}" @selected($settings['dokumen']['storage_location'] === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <span class="form-label">Tipe File yang Diizinkan</span>
                                <div class="flex flex-wrap gap-4">
                                    @foreach (['pdf', 'png', 'jpg', 'doc'] as $type)
                                        <label class="flex items-center gap-2 text-sm text-body">
                                            <input type="checkbox" name="allowed_types[]" value="{{ $type }}"
                                                   @checked(in_array($type, $settings['dokumen']['allowed_types'])) class="size-4 rounded border-line text-primary focus:ring-primary/20">
                                            .{{ $type }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($key === 'backup')
                            <div>
                                <x-input-label for="jadwal" value="Jadwal Backup" />
                                <select id="jadwal" name="jadwal" class="form-input">
                                    @foreach (['daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan'] as $value => $label)
                                        <option value="{{ $value }}" @selected($settings['backup']['jadwal'] === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="waktu" value="Waktu Backup" />
                                <input type="time" id="waktu" name="waktu" value="{{ $settings['backup']['waktu'] }}" class="form-input">
                            </div>
                            <div>
                                <x-input-label for="api_url" value="API URL" />
                                <input type="url" id="api_url" name="api_url" value="{{ $settings['backup']['api_url'] }}" class="form-input" placeholder="https://...">
                            </div>
                            <div>
                                <x-input-label for="api_key" value="API Key" />
                                <input type="password" id="api_key" name="api_key" class="form-input" placeholder="••••••••">
                                <p class="mt-1.5 text-xs text-muted">Kosongkan untuk mempertahankan API key yang sudah tersimpan.</p>
                            </div>
                        @endif

                        <div class="sm:col-span-2 flex justify-end border-t border-line pt-5">
                            <button type="submit" class="btn-primary">
                                <x-icon name="check" class="w-4 h-4" />
                                Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
