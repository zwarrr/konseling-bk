@props(['pageParam' => 'page', 'windowParam' => 'pw'])
@if ($paginator->hasPages())
@php
  $current = $paginator->currentPage();
  $last    = $paginator->lastPage();

  // Resolve window start from query string; default: centered on current page
  $pw = (int) request($windowParam, 0);
  if ($pw < 1) $pw = max(1, $current - 1);

  // Clamp window so it never exceeds available pages
  $pw = min($pw, max(1, $last - 2));
  $pw = max(1, $pw);

  // If the current page falls outside the displayed window, snap window to include it
  if ($current < $pw)       $pw = $current;
  if ($current > $pw + 2)   $pw = max(1, $current - 2);

  // Build the 3-page window
  $windowPages = [];
  for ($i = $pw; $i <= min($pw + 2, $last); $i++) {
      $windowPages[] = $i;
  }

  // Can arrows shift further?
  $canShiftLeft  = $pw > 1;
  $canShiftRight = ($pw + 2) < $last;

  $leftUrl  = request()->fullUrlWithQuery([$windowParam => max(1, $pw - 1),                    $pageParam => $current]);
  $rightUrl = request()->fullUrlWithQuery([$windowParam => min(max(1, $last - 2), $pw + 1),    $pageParam => $current]);
@endphp
<nav class="flex items-center justify-between px-6 py-4 border-t border-gray-100" aria-label="Pagination">

  {{-- Info --}}
  <p class="text-sm text-gray-500">
    Menampilkan
    <span class="font-medium text-gray-700">{{ $paginator->firstItem() }}</span>–<span class="font-medium text-gray-700">{{ $paginator->lastItem() }}</span>
    dari
    <span class="font-medium text-gray-700">{{ $paginator->total() }}</span>
    data
  </p>

  {{-- Controls --}}
  <div class="flex items-center gap-1">

    {{-- Shift window left --}}
    @if ($canShiftLeft)
      <a href="{{ $leftUrl }}"
        class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-500 hover:bg-gray-100 transition"
        aria-label="Geser kiri">
        <i class="fa-solid fa-chevron-left text-xs"></i>
      </a>
    @else
      <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-300 cursor-not-allowed select-none">
        <i class="fa-solid fa-chevron-left text-xs"></i>
      </span>
    @endif

    {{-- 3-page window --}}
    @foreach ($windowPages as $page)
      @if ($page == $current)
        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-blue-600 text-white text-sm font-semibold select-none">
          {{ $page }}
        </span>
      @else
        <a href="{{ request()->fullUrlWithQuery([$pageParam => $page, $windowParam => $pw]) }}"
          class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-600 hover:bg-gray-100 text-sm transition">
          {{ $page }}
        </a>
      @endif
    @endforeach

    {{-- Shift window right --}}
    @if ($canShiftRight)
      <a href="{{ $rightUrl }}"
        class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-500 hover:bg-gray-100 transition"
        aria-label="Geser kanan">
        <i class="fa-solid fa-chevron-right text-xs"></i>
      </a>
    @else
      <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-300 cursor-not-allowed select-none">
        <i class="fa-solid fa-chevron-right text-xs"></i>
      </span>
    @endif

  </div>
</nav>
@endif
