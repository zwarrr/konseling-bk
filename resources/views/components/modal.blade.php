@props([
  'id',
  'title',
  'subtitle' => null,
  'tone' => 'info', // info|success|warning|error
])

@php
  $toneTitleClass = match ($tone) {
    'success' => 'text-emerald-700',
    'warning' => 'text-amber-700',
    'error' => 'text-red-700',
    default => 'text-slate-900',
  };
@endphp

<div id="{{ $id }}" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40" data-close-modal="{{ $id }}"></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl border border-slate-200 shadow-xl">
      <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
        <div>
          @if($subtitle)
            <div class="text-xs text-slate-500">{{ $subtitle }}</div>
          @endif
          <div class="text-lg font-semibold {{ $toneTitleClass }}">{{ $title }}</div>
        </div>
        <button type="button" class="w-9 h-9 rounded-xl border border-slate-200 hover:bg-slate-50 transition" data-close-modal="{{ $id }}">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="p-6">
        {{ $slot }}

        <div class="pt-4 flex justify-end">
          @isset($footer)
            {{ $footer }}
          @else
            <button type="button" class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-600/90 transition" data-close-modal="{{ $id }}">Oke</button>
          @endisset
        </div>
      </div>
    </div>
  </div>
</div>
