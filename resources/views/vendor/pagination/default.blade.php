@if ($paginator->hasPages())
<nav aria-label="...">
    <ul class="pagination justify-content-end">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <!-- <li class="disabled"><span>&laquo;</span></li> -->
            <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1" aria-disabled="true" data-bs-toggle="modal" data-bs-target="#modal-spinner">Previous</a>
            </li>
        @else
            <!-- <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo;</a></li> -->
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" tabindex="-1" aria-disabled="true" data-bs-toggle="modal" data-bs-target="#modal-spinner">Previous</a>
            </li>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <li class="page-item disabled">
                    <span class="page-link" data-bs-toggle="modal" data-bs-target="#modal-spinner">{{ $element }}</span>
                </li>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <!-- <li class="active"><span>{{ $page }}</span></li> -->
                        <li class="page-item active" aria-current="page">
                            <a class="page-link" href="#" data-bs-toggle="modal" data-bs-target="#modal-spinner">{{ $page }}</a>
                        </li>
                    @else
                        <!-- <li><a href="{{ $url }}">{{ $page }}</a></li> -->
                        <li class="page-item">
                            <a class="page-link" href="{{ $url }}" data-bs-toggle="modal" data-bs-target="#modal-spinner">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <!-- <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">&raquo;</a></li> -->
            <li class="page-item">
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
            </li>
        @else
            <!-- <li class="disabled"><span>&raquo;</span></li> -->
            <li class="page-item disabled">
                <a class="page-link" href="#" aria-disabled="true">Next</a>
            </li>
        @endif
    </ul>
</nav>
@endif

@push('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        $('.page-link').click(function(e) {
            location.href = $(this).attr('href');
        });
    });
</script>
@endpush