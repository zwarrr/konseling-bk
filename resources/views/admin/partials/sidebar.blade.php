<aside id="sidebar" class="fixed inset-y-0 left-0 z-30 w-60 bg-white border-r border-slate-200 transform -translate-x-full md:translate-x-0 transition-transform duration-200 flex flex-col">
  <div class="px-5 py-5 border-b border-slate-200">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center">
        <i class="fa-solid fa-shield-halved"></i>
      </div>
      <div>
        <h2 class="text-sm font-semibold text-slate-900 leading-tight">Konseling BK</h2>
        <p class="text-xs text-slate-500">Admin Panel</p>
      </div>
    </div>
  </div>

  <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
    <a href="{{ route('admin.dashboard.index') }}"
       class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition border {{ request()->routeIs('admin.dashboard.*') ? 'bg-blue-600/10 border-blue-600/10 text-blue-600' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
      <i class="fa-solid fa-chart-simple w-5 text-center"></i>
      <span class="font-medium">Dashboard</span>
    </a>

    <a href="{{ route('admin.accounts.index') }}"
       class="group flex items-center gap-3 px-3 py-2.5 rounded-xl transition border {{ request()->routeIs('admin.accounts.*') ? 'bg-blue-600/10 border-blue-600/10 text-blue-600' : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
      <i class="fa-solid fa-users w-5 text-center"></i>
      <span class="font-medium">Kelola Akun</span>
    </a>
  </nav>

  <div class="px-3 pb-4">
    <button id="adminLogoutBtn" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition">
      <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>
      <span class="font-medium">Logout</span>
    </button>
  </div>
</aside>
