<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center px-6 py-12 bg-canvas">
        <div class="w-full max-w-md">
            <h2 class="text-ink text-[26px] font-semibold tracking-tight">Konfirmasi kata sandi</h2>
            <p class="mt-1.5 mb-8 text-sm text-body">Ini adalah area aman. Konfirmasikan kata sandi Anda sebelum melanjutkan.</p>

            <div class="bg-surface border border-line rounded-2xl p-8">
                <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
                    @csrf

                    <!-- Password -->
                    <div>
                        <x-input-label for="password" :value="__('Password')" />

                        <x-text-input id="password" class="block mt-1 w-full"
                                        type="password"
                                        name="password"
                                        required autocomplete="current-password" />

                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <x-primary-button class="w-full justify-center">
                        {{ __('Confirm') }}
                    </x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
