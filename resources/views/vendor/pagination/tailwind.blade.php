@if ($paginator->hasPages())
    <nav class="ceet-clean-pagination" role="navigation" aria-label="Pagination">
        <div class="ceet-clean-pagination-arrows">
            @if ($paginator->onFirstPage())
                <span class="ceet-clean-pagination-btn is-disabled" aria-disabled="true" aria-label="Page précédente">
                    <span aria-hidden="true">‹</span>
                </span>
            @else
                <a
                    href="{{ $paginator->previousPageUrl() }}"
                    rel="prev"
                    class="ceet-clean-pagination-btn"
                    aria-label="Page précédente"
                    data-ceet-link
                >
                    <span aria-hidden="true">‹</span>
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a
                    href="{{ $paginator->nextPageUrl() }}"
                    rel="next"
                    class="ceet-clean-pagination-btn"
                    aria-label="Page suivante"
                    data-ceet-link
                >
                    <span aria-hidden="true">›</span>
                </a>
            @else
                <span class="ceet-clean-pagination-btn is-disabled" aria-disabled="true" aria-label="Page suivante">
                    <span aria-hidden="true">›</span>
                </span>
            @endif
        </div>
    </nav>
@endif
