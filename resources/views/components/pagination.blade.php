{{-- Stronicowanie listy (.ss-period__nav): poprzednia i następna strona jako przyciski z ikonami, „Strona X z Y” i liczba pozycji; przy jednej stronie nic. paginator: Illuminate\Contracts\Pagination\LengthAwarePaginator. --}}
@props(['paginator'])
@if ($paginator->lastPage() > 1)
    <nav {{ $attributes->merge(['class' => 'ss-period__nav']) }} aria-label="{{ text('layout.pagination.label') }}">
        @if ($paginator->previousPageUrl() !== null)
            <a class="ss-icon-button" href="{{ $paginator->previousPageUrl() }}" rel="prev" title="{{ text('layout.pagination.previous') }}"><x-mainboard::icon name="arrow-left" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('layout.pagination.previous') }}</span></a>
        @endif
        <span class="ss-period__label">{{ text('layout.pagination.page', ['page' => local_number($paginator->currentPage()), 'pages' => local_number($paginator->lastPage())]) }}</span>
        <span class="ss-muted">{{ text('layout.pagination.total', ['count' => local_number($paginator->total())]) }}</span>
        @if ($paginator->nextPageUrl() !== null)
            <a class="ss-icon-button" href="{{ $paginator->nextPageUrl() }}" rel="next" title="{{ text('layout.pagination.next') }}"><x-mainboard::icon name="arrow-right" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('layout.pagination.next') }}</span></a>
        @endif
    </nav>
@endif
