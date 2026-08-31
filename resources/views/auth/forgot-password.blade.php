<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center px-6 py-12 bg-canvas">
        <div class="w-full max-w-md">
            <h2 class="text-ink text-[26px] font-semibold tracking-tight">Lupa kata sandi</h2>
            <p class="mt-1.5 mb-8 text-sm text-body">Masukkan email Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi.</p>

            <div class="bg-surface border border-line rounded-2xl p-8">
                <!-- Session Status -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                    @csrf

                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <x-primary-button class="w-full justify-center">
                        {{ __('Kirim Tautan Reset') }}
                    </x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
