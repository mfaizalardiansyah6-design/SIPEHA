@props(['title' => '', 'description' => ''])

<div class="mb-8 flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="page-title">{{ $title }}</h1>
        @if ($description)
            <p class="text-sm text-muted mt-2">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex items-center gap-3">
            {{ $actions }}
        </div>
    @endisset
</div>
