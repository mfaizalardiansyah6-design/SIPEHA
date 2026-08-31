@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}">
        <div class="flex items-center justify-between gap-4 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center gap-1 rounded-full border border-line bg-canvas-parchment px-4 py-2 text-sm text-muted cursor-not-allowed">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-1 rounded-full border border-line bg-surface px-4 py-2 text-sm text-body hover:border-primary hover:text-primary transition-colors">
                    {!! __('pagination.previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-1 rounded-full border border-line bg-surface px-4 py-2 text-sm text-body hover:border-primary hover:text-primary transition-colors">
                    {!! __('pagination.next') !!}
                </a>
            @else
                <span class="inline-flex items-center gap-1 rounded-full border border-line bg-canvas-parchment px-4 py-2 text-sm text-muted cursor-not-allowed">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        <div class="hidden sm:flex sm:items-center sm:justify-between sm:gap-4">
            <div>
                <p class="text-sm text-muted">
                    {!! __('Showing') !!}
                    @if ($paginator->firstItem())
                        <span class="font-semibold text-body">{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="font-semibold text-body">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    {!! __('of') !!}
                    <span class="font-semibold text-body">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>

            <div class="flex items-center gap-1.5">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}"
                          class="inline-flex items-center justify-center size-9 rounded-full border border-line text-muted cursor-not-allowed">
                        <x-icon name="chevron-left" class="w-4 h-4" />
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}"
                       class="inline-flex items-center justify-center size-9 rounded-full border border-line text-body hover:border-primary hover:text-primary transition-colors">
                        <x-icon name="chevron-left" class="w-4 h-4" />
                    </a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span aria-disabled="true" class="inline-flex items-center justify-center size-9 rounded-full text-sm text-muted">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page"
                                      class="inline-flex items-center justify-center size-9 rounded-full bg-primary text-white text-sm font-semibold">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                                   class="inline-flex items-center justify-center size-9 rounded-full border border-line text-sm text-body hover:border-primary hover:text-primary transition-colors">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}"
                       class="inline-flex items-center justify-center size-9 rounded-full border border-line text-body hover:border-primary hover:text-primary transition-colors">
                        <x-icon name="chevron-right" class="w-4 h-4" />
                    </a>
                @else
                    <span aria-disabled="true" aria-label="{{ __('pagination.next') }}"
                          class="inline-flex items-center justify-center size-9 rounded-full border border-line text-muted cursor-not-allowed">
                        <x-icon name="chevron-right" class="w-4 h-4" />
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
