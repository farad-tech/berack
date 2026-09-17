@props(['paginator'])
@if ($paginator->hasPages())
<nav class="pagination" aria-label="{{ __('panel.page') }}">
    @if ($paginator->previousPageUrl())<a class="icon-button" href="{{ $paginator->previousPageUrl() }}" aria-label="{{ __('panel.previous') }}" title="{{ __('panel.previous') }}"><x-panel.icon name="chevron-right" class="direction-icon" /></a>@else<span class="icon-button disabled"><x-panel.icon name="chevron-right" class="direction-icon" /></span>@endif
    <span>{{ __('panel.page_of', ['page' => $paginator->currentPage(), 'total' => $paginator->lastPage()]) }}</span>
    @if ($paginator->nextPageUrl())<a class="icon-button" href="{{ $paginator->nextPageUrl() }}" aria-label="{{ __('panel.next') }}" title="{{ __('panel.next') }}"><x-panel.icon name="chevron-left" class="direction-icon" /></a>@else<span class="icon-button disabled"><x-panel.icon name="chevron-left" class="direction-icon" /></span>@endif
</nav>
@endif
