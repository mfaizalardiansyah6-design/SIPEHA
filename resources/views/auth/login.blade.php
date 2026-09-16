<x-guest-layout>
    <div class="min-h-screen flex bg-surface">
        <div class="flex-1 flex items-center justify-center px-6 py-12 lg:px-12">
            <div class="w-full max-w-md">
                <div class="flex items-center gap-3 mb-10">
                    @if (! empty($instansiLogoUrl))
                        <img src="{{ $instansiLogoUrl }}" alt="{{ $instansi['nama'] }}" class="size-12 object-contain shrink-0">
                    @else
                        <div class="size-12 rounded-lg bg-primary flex items-center justify-center text-white text-base font-semibold overflow-hidden">
                            {{ strtoupper(substr($instansi['nama'] ?? 'H', 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h1 class="text-ink text-xl font-semibold tracking-tight leading-tight">{{ $instansi['nama'] ?? 'SIMHAK WBP' }}</h1>
                        <p class="text-xs text-muted">{{ $instansi['kode'] ?: 'Sistem Monitoring Pemenuhan Hak Warga Binaan' }}</p>
                    </div>
                </div>

                <h2 class="text-ink text-[26px] font-semibold tracking-tight">Masuk</h2>
                <p class="mt-1.5 mb-8 text-sm text-body">Gunakan NIP atau email serta kata sandi Anda.</p>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <x-input-label for="identity" :value="__('NIP / Email')" />
                        <x-text-input id="identity" class="block mt-1 w-full" type="text" name="identity" :value="old('identity')" required autofocus autocomplete="username" placeholder="198701012010011001" />
                        <x-input-error :messages="$errors->get('identity')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password" :value="__('Kata Sandi')" />
                        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" class="rounded border-line text-primary focus:ring-primary" name="remember">
                            <span class="ms-2 text-sm text-body">{{ __('Ingat saya') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm text-primary hover:text-primary-dark" href="{{ route('password.request') }}">
                                {{ __('Lupa kata sandi?') }}
                            </a>
                        @endif
                    </div>

                    <x-primary-button class="w-full justify-center py-3">
                        {{ __('Masuk') }}
                    </x-primary-button>
                </form>
            </div>
        </div>

        <div class="hidden lg:flex flex-1 bg-surface-tile-1 relative items-center justify-center overflow-hidden">
            <svg class="absolute inset-0 w-full h-full" viewBox="0 0 800 800" fill="none" preserveAspectRatio="xMidYMid slice">
                <rect width="800" height="800" fill="#272729" />
                <circle cx="600" cy="180" r="160" stroke="#3a3a3c" stroke-width="1.5" />
                <circle cx="600" cy="180" r="110" stroke="#48484a" stroke-width="1.5" />
                <circle cx="600" cy="180" r="60" fill="#0066CC" opacity="0.25" />
                <rect x="80" y="560" width="220" height="220" rx="24" transform="rotate(-12 80 560)" fill="#2f2f31" stroke="#3a3a3c" stroke-width="1.5" />
                <rect x="140" y="620" width="60" height="6" rx="3" fill="#48484a" />
                <rect x="140" y="640" width="100" height="6" rx="3" fill="#3a3a3c" />
                <rect x="140" y="660" width="80" height="6" rx="3" fill="#3a3a3c" />
                <path d="M560 520 L640 600 L520 600 Z" fill="#2997FF" opacity="0.14" />
                <rect x="420" y="420" width="90" height="90" rx="16" fill="#2f2f31" stroke="#3a3a3c" stroke-width="1.5" />
                <rect x="438" y="448" width="30" height="5" rx="2.5" fill="#2997FF" />
                <rect x="438" y="466" width="54" height="5" rx="2.5" fill="#3a3a3c" />
                <line x1="300" y1="680" x2="660" y2="680" stroke="#3a3a3c" stroke-width="1.5" stroke-dasharray="6 8" />
            </svg>

            <div class="relative max-w-sm text-center px-8">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-primary px-3 py-1 text-xs font-semibold text-white mb-8">
                    <x-icon name="shield-check" class="w-4 h-4" />
                    {{ $instansi['nama'] ?? 'SIMHAK WBP' }}
                </span>
                <h2 class="text-white text-3xl font-semibold tracking-tight leading-tight">Monitor hak Warga Binaan dengan transparan dan terpusat</h2>
                <p class="mt-4 text-sm text-white/60 leading-relaxed">Pencatatan digital layanan hak warga binaan — video call, perawatan diri, pemeriksaan kesehatan, hingga layanan laundry — dalam satu sistem.</p>
            </div>
        </div>
    </div>
</x-guest-layout>
