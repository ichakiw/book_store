@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" class="custom-pagination-nav">
        <div class="custom-pagination-info">
            Menampilkan <span class="font-bold">{{ $paginator->firstItem() }}</span> – <span class="font-bold">{{ $paginator->lastItem() }}</span> dari <span class="font-bold">{{ $paginator->total() }}</span> data
        </div>

        <ul class="custom-pagination-list">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="custom-page-item disabled" aria-disabled="true">
                    <span class="custom-page-link prev">‹ Sebelumnya</span>
                </li>
            @else
                <li class="custom-page-item">
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="custom-page-link prev">‹ Sebelumnya</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="custom-page-item disabled" aria-disabled="true">
                        <span class="custom-page-link dots">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="custom-page-item active" aria-current="page">
                                <span class="custom-page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="custom-page-item">
                                <a href="{{ $url }}" class="custom-page-link">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="custom-page-item">
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="custom-page-link next">Selanjutnya ›</a>
                </li>
            @else
                <li class="custom-page-item disabled" aria-disabled="true">
                    <span class="custom-page-link next">Selanjutnya ›</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
