{{--
  Partial: shared/partials/agenda-reviews.blade.php
  Variables expected (from parent view):
    $reviewsAvg     – float average rating
    $reviewsCount   – int total reviews
    $reviews        – paginated AgendaReview collection
    $firstReviewId  – int|null id of oldest review (highlight)
    $reviewStoreUrl – string POST url for submitting a review
    $authUser       – auth user or null
--}}

{{-- Flash success --}}
@if(session('review_success'))
<div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 text-sm font-medium px-4 py-3 rounded-xl">
  <i class="fa-solid fa-circle-check text-green-500"></i>
  {{ session('review_success') }}
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

  {{-- ══ LEFT: avg display + write-review form ══ --}}
  <div>

    {{-- Avg star display --}}
    <div class="flex items-center gap-4 p-5 bg-white rounded-2xl border border-gray-100 shadow-sm mb-6">
      <div class="text-center shrink-0">
        <div class="text-4xl font-extrabold text-gray-900 leading-none">{{ number_format($reviewsAvg, 1) }}</div>
        <div class="flex justify-center gap-0.5 mt-1.5">
          @for($s = 1; $s <= 5; $s++)
            <i class="fa-{{ $s <= round($reviewsAvg) ? 'solid' : 'regular' }} fa-star text-yellow-400 text-sm"></i>
          @endfor
        </div>
        <div class="text-xs text-gray-400 mt-1">{{ $reviewsCount }} ulasan</div>
      </div>
      <div class="flex-1 min-w-0">
        @for($s = 5; $s >= 1; $s--)
        @php
          $cnt = $reviewsCount > 0
            ? (int) $reviews->getCollection()->where('rating', $s)->count()
            : 0;
          $pct = $reviewsCount > 0 ? round($cnt / $reviewsCount * 100) : 0;
        @endphp
        <div class="flex items-center gap-2 mb-1">
          <span class="text-xs text-gray-500 w-3 shrink-0">{{ $s }}</span>
          <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full bg-yellow-400 rounded-full" style="width:{{ $pct }}%"></div>
          </div>
        </div>
        @endfor
      </div>
    </div>

    {{-- Write-review form --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
      <h3 class="text-sm font-bold text-gray-900 mb-4">Tulis Ulasan</h3>
      <form action="{{ $reviewStoreUrl }}" method="POST"
            x-data="{ rating: 0, hover: 0, charCount: 0 }">
        @csrf

        {{-- Name --}}
        <div class="mb-4">
          <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama</label>
          <input type="text" name="name" maxlength="100"
            placeholder="Nama Anda"
            value="{{ old('name', $authUser->name ?? '') }}"
            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition"
            required>
        </div>

        {{-- Star picker --}}
        <div class="mb-4">
          <label class="block text-xs font-semibold text-gray-600 mb-1.5">Rating</label>
          <div class="flex gap-1.5">
            @for($s = 1; $s <= 5; $s++)
            <button type="button"
              @mouseenter="hover = {{ $s }}"
              @mouseleave="hover = 0"
              @click="rating = {{ $s }}"
              class="text-2xl transition focus:outline-none"
              aria-label="{{ $s }} bintang">
              <span :class="(hover || rating) >= {{ $s }} ? 'text-yellow-400' : 'text-gray-200'">
                <i class="fa-solid fa-star"></i>
              </span>
            </button>
            @endfor
          </div>
          <input type="hidden" name="rating" :value="rating" required>
          <p class="text-xs text-gray-400 mt-1" x-show="rating === 0">Pilih bintang rating</p>
        </div>

        {{-- Comment --}}
        <div class="mb-4">
          <label class="block text-xs font-semibold text-gray-600 mb-1.5">
            Komentar <span class="text-gray-300 font-normal">(opsional)</span>
          </label>
          <textarea name="comment" rows="3" maxlength="500"
            @input="charCount = $el.value.length"
            placeholder="Ceritakan pengalaman Anda mengikuti kegiatan ini..."
            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm resize-none focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition">{{ old('comment') }}</textarea>
          <p class="text-right text-xs text-gray-400 mt-1"><span x-text="charCount">0</span> / 500</p>
        </div>

        <button type="submit"
          :disabled="rating === 0"
          :class="rating === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-800'"
          class="btn-raise w-full bg-blue-700 text-white font-bold text-sm py-3 rounded-xl shadow-md shadow-blue-700/20 transition">
          <i class="fa-solid fa-paper-plane mr-1.5 text-xs"></i> Kirim Ulasan
        </button>
      </form>
    </div>

  </div>

  {{-- ══ RIGHT: reviews list ══ --}}
  <div id="reviews-list">

    @if($reviews->count())
      <div class="space-y-4">
        @foreach($reviews as $review)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 {{ $firstReviewId === $review->id ? 'ring-2 ring-blue-100' : '' }}">
          <div class="flex items-start justify-between gap-2 mb-2">
            <div class="flex items-center gap-2.5">
              <div class="w-9 h-9 rounded-full bg-blue-50 border border-blue-100 flex items-center justify-center shrink-0">
                <span class="text-sm font-bold text-blue-700">{{ mb_strtoupper(mb_substr($review->name, 0, 1)) }}</span>
              </div>
              <div>
                <p class="text-sm font-semibold text-gray-900 leading-tight">{{ $review->name }}</p>
                <p class="text-xs text-gray-400">
                  {{ $review->created_at->diffInDays(now()) < 7
                      ? $review->created_at->diffForHumans()
                      : $review->created_at->format('d M Y') }}
                </p>
              </div>
            </div>
            <div class="flex gap-0.5 shrink-0">
              @for($s = 1; $s <= 5; $s++)
                <i class="fa-{{ $s <= $review->rating ? 'solid' : 'regular' }} fa-star text-yellow-400 text-xs"></i>
              @endfor
            </div>
          </div>
          @if($review->comment)
          <p class="text-sm text-gray-600 leading-relaxed line-clamp-3">{{ $review->comment }}</p>
          @endif
        </div>
        @endforeach
      </div>

      {{-- Pagination --}}
      @if($reviews->hasPages())
      <div class="flex items-center justify-between mt-5">
        @if($reviews->onFirstPage())
          <span class="text-xs text-gray-300 px-4 py-2 rounded-lg border border-gray-100">← Sebelumnya</span>
        @else
          <a href="{{ $reviews->previousPageUrl() }}#reviews-list"
            class="text-xs text-gray-600 hover:text-blue-700 px-4 py-2 rounded-lg border border-gray-200 hover:border-blue-300 transition">
            ← Sebelumnya
          </a>
        @endif
        <span class="text-xs text-gray-400">{{ $reviews->currentPage() }} / {{ $reviews->lastPage() }}</span>
        @if($reviews->hasMorePages())
          <a href="{{ $reviews->nextPageUrl() }}#reviews-list"
            class="text-xs text-gray-600 hover:text-blue-700 px-4 py-2 rounded-lg border border-gray-200 hover:border-blue-300 transition">
            Selanjutnya →
          </a>
        @else
          <span class="text-xs text-gray-300 px-4 py-2 rounded-lg border border-gray-100">Selanjutnya →</span>
        @endif
      </div>
      @endif

    @else
      <div class="flex flex-col items-center justify-center py-20 text-center">
        <i class="fa-regular fa-comment-dots text-gray-200 text-5xl mb-4"></i>
        <p class="text-sm font-semibold text-gray-400">Belum ada ulasan</p>
        <p class="text-xs text-gray-300 mt-1">Jadilah yang pertama memberikan ulasan!</p>
      </div>
    @endif

  </div>

</div>
