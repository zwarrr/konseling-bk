@extends('users.layout')

@section('title', 'Buku Panduan — ' . config('app.name', 'E-Konseling'))
@section('hideBottomBar', true)

@section('content')
@php
  $userName = $user->name ?? 'Pengguna';
@endphp

<div class="min-h-full bg-slate-50">
  <header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-slate-200" style="padding-top:env(safe-area-inset-top,0px)">
    <div class="px-4 md:px-8 py-3 flex items-center gap-3">
      <a href="{{ $backRoute }}"
         class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 bg-slate-100 hover:bg-slate-200 transition">
        <i class="fa-solid fa-arrow-left text-slate-600 text-sm"></i>
      </a>
      <div>
        <h1 class="text-base md:text-lg font-extrabold text-slate-900">Buku Panduan {{ $roleLabel }}</h1>
        <p class="text-xs text-slate-500">Panduan fitur utama E-Konseling sesuai role kamu</p>
      </div>
    </div>
  </header>

  <div class="px-4 md:px-8 py-6 pb-10">
    <div class="max-w-4xl mx-auto space-y-4">
      <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 md:p-5">
        <p class="text-sm text-blue-900 leading-relaxed">
          Halo {{ $userName }}, berikut panduan singkat agar kamu bisa menggunakan E-Konseling dengan lebih efektif.
        </p>
      </div>

      @foreach($steps as $index => $section)
      <section class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
          <h2 class="text-sm md:text-base font-bold text-slate-900">{{ $index + 1 }}. {{ $section['title'] }}</h2>
        </div>
        <div class="px-5 py-4">
          <ul class="space-y-2">
            @foreach($section['items'] as $item)
            <li class="text-sm text-slate-700 leading-relaxed flex items-start gap-2">
              <span class="mt-1 w-1.5 h-1.5 rounded-full bg-blue-500 shrink-0"></span>
              <span>{{ $item }}</span>
            </li>
            @endforeach
          </ul>
        </div>
      </section>
      @endforeach

      <section class="bg-amber-50 border border-amber-200 rounded-2xl p-4 md:p-5">
        <h3 class="text-sm font-bold text-amber-900 mb-2">Tips Penggunaan</h3>
        <p class="text-sm text-amber-900 leading-relaxed">
          Pastikan data profil kamu selalu terbaru, aktifkan notifikasi, dan gunakan bahasa yang jelas saat berinteraksi agar komunikasi dengan pihak BK lebih efektif.
        </p>
      </section>
    </div>
  </div>
</div>
@endsection
