@props([
    'label' => '',
    'value' => '',
    'subtitle' => '',
    'icon' => '',
    'tone' => 'neutral',
    'valueClass' => 'text-ink',
])

@php
    $tones = [
        'neutral' => 'bg-ink/5 text-ink',
        'blue' => 'bg-primary/10 text-primary',
        'teal' => 'bg-teal-brand/10 text-teal-dark',
        'orange' => 'bg-orange-500/10 text-orange-600',
        'red' => 'bg-red-500/10 text-red-600',
    ];
    $iconClass = $tones[$tone] ?? $tones['neutral'];
@endphp

<div class="card card-padding h-full transition-colors hover:border-line-dark">
    <div class="flex items-center gap-4 min-w-0">
        @if ($icon)
            <div class="stat-card-icon {{ $iconClass }}">
                <x-icon :name="$icon" class="w-5 h-5" />
            </div>
        @endif

        <div class="min-w-0">
            <p class="text-xs text-muted font-semibold truncate">{{ $label }}</p>
            <p class="text-[28px] font-semibold tracking-[-0.374px] {{ $valueClass }} mt-1">{{ $value }}</p>
            @if ($subtitle)
                <p class="text-xs text-muted truncate mt-1">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
</div>
