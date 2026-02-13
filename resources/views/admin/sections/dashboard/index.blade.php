@extends('admin.layout')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
  <div class="bg-white rounded-xl border border-gray-200 p-5">
    <div class="flex items-center gap-3 mb-2">
      <div class="w-10 h-10 rounded-lg bg-blue-600 text-white flex items-center justify-center">
        <i class="fa-solid fa-envelope text-sm"></i>
      </div>
      <span class="text-sm text-slate-500">Total Pesan</span>
    </div>
    <p class="text-2xl font-bold text-slate-900">{{ number_format($totalMessages) }}</p>
  </div>

  <div class="bg-white rounded-xl border border-gray-200 p-5">
    <div class="flex items-center gap-3 mb-2">
      <div class="w-10 h-10 rounded-lg bg-blue-600 text-white flex items-center justify-center">
        <i class="fa-solid fa-users text-sm"></i>
      </div>
      <span class="text-sm text-slate-500">Total Akun</span>
    </div>
    <p class="text-2xl font-bold text-slate-900">{{ number_format($totalUsers) }}</p>
  </div>

  <div class="bg-white rounded-xl border border-gray-200 p-5">
    <div class="flex items-center gap-3 mb-2">
      <div class="w-10 h-10 rounded-lg bg-blue-600 text-white flex items-center justify-center">
        <i class="fa-solid fa-user-tie text-sm"></i>
      </div>
      <span class="text-sm text-slate-500">Guru BK</span>
    </div>
    <p class="text-2xl font-bold text-slate-900">{{ number_format($totalBK) }}</p>
  </div>

  <div class="bg-white rounded-xl border border-gray-200 p-5">
    <div class="flex items-center gap-3 mb-2">
      <div class="w-10 h-10 rounded-lg bg-blue-600 text-white flex items-center justify-center">
        <i class="fa-solid fa-graduation-cap text-sm"></i>
      </div>
      <span class="text-sm text-slate-500">Siswa</span>
    </div>
    <p class="text-2xl font-bold text-slate-900">{{ number_format($totalSiswa) }}</p>
  </div>
</div>
@endsection
