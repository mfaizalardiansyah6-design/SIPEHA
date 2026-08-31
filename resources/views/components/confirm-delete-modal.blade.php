@props(['title' => 'Konfirmasi Hapus', 'message' => 'Data yang dihapus tidak dapat dikembalikan.', 'buttonLabel' => 'Hapus'])

<div x-data="confirmModal" x-cloak>
    <div x-show="show" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" @click="close()"></div>

        <div class="relative card card-padding w-full max-w-md whitespace-normal" x-show="show">
            <div class="flex items-start gap-4">
                <div class="size-10 rounded-lg bg-red-500/10 flex items-center justify-center text-red-600 shrink-0">
                    <x-icon name="exclamation-triangle" class="w-6 h-6" />
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-base font-semibold text-ink break-words">{{ $title }}</h2>
                    <p class="mt-1 text-sm text-body break-words">{{ $message }}</p>
                </div>
            </div>

            <form x-ref="form" :action="action" method="POST" class="mt-6 flex items-center justify-end gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="close()" class="btn-secondary">
                    Batal
                </button>
                <button type="submit" class="btn-danger">
                    {{ $buttonLabel }}
                </button>
            </form>
        </div>
    </div>

    {{ $slot }}
</div>
