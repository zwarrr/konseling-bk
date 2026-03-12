@props([
  'id',
  'title',
  'subtitle' => 'Pemberitahuan',
  'tone' => 'info', // info|success|warning|error
])

@php
  $topBar       = match ($tone) {
    'success' => 'bg-emerald-600',
    'warning' => 'bg-amber-500',
    'error'   => 'bg-red-600',
    default   => 'bg-blue-600',
  };
  $iconBg       = match ($tone) {
    'success' => 'bg-emerald-100',
    'warning' => 'bg-amber-100',
    'error'   => 'bg-red-100',
    default   => 'bg-blue-100',
  };
  $iconColor    = match ($tone) {
    'success' => 'text-emerald-600',
    'warning' => 'text-amber-600',
    'error'   => 'text-red-600',
    default   => 'text-blue-600',
  };
  $icon         = match ($tone) {
    'success' => 'fa-circle-check',
    'warning' => 'fa-triangle-exclamation',
    'error'   => 'fa-circle-xmark',
    default   => 'fa-circle-info',
  };
  $badgeBg      = match ($tone) {
    'success' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
    'warning' => 'bg-amber-50 border-amber-200 text-amber-800',
    'error'   => 'bg-red-50 border-red-200 text-red-800',
    default   => 'bg-blue-50 border-blue-200 text-blue-800',
  };
  $btnClass     = match ($tone) {
    'success' => 'bg-emerald-600 hover:bg-emerald-700',
    'warning' => 'bg-amber-600 hover:bg-amber-700',
    'error'   => 'bg-red-600 hover:bg-red-700',
    default   => 'bg-blue-600 hover:bg-blue-700',
  };
@endphp

<div id="{{ $id }}" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px]" data-close-modal="{{ $id }}"></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-sm bg-white rounded-2xl shadow-2xl p-8 text-center">

      <div class="w-16 h-16 rounded-full {{ $iconBg }} flex items-center justify-center mx-auto mb-5">
        <i class="fa-solid {{ $icon }} text-2xl {{ $iconColor }}"></i>
      </div>

      <div class="text-xl font-bold text-slate-900 mb-2">{{ $title }}</div>

      {{-- Message slot --}}
      <div class="text-sm text-slate-500 mb-8 leading-relaxed">
        {{ $slot }}
      </div>

      <div>
        @isset($footer)
          {{ $footer }}
        @else
          <button type="button"
            class="w-full py-2.5 rounded-xl {{ $btnClass }} text-white text-sm font-semibold transition"
            data-close-modal="{{ $id }}">Oke</button>
        @endisset
      </div>

    </div>
  </div>
</div>
