@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-semibold text-sm text-teal-dark']) }}>
        {{ $status }}
    </div>
@endif
