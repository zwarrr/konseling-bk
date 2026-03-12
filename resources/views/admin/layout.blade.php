<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin') — {{ config('app.name') }}</title>
  <link rel="icon" href="/favicon.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{primary:'#0f4c9a',blue:{600:'#0f4c9a',700:'#0a3d80'}}}}}</script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box}
    html,body{margin:0;padding:0;height:100%;overflow:hidden}
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
  </style>
</head>
<body class="h-screen overflow-hidden bg-slate-100 text-slate-800">
  <x-splash-screen :user-name="auth('admin')->user()?->name" :no-reload="true" />
  @include('admin.partials.sidebar')

  <main class="fixed top-0 right-0 bottom-0 z-10 overflow-y-auto">
    <header class="sticky top-0 z-10 bg-white/90 backdrop-blur border-b border-slate-200">
      <div class="px-6 py-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
          <div>
            <h1 class="text-lg md:text-xl font-semibold text-slate-900">@yield('heading', 'Admin')</h1>
          </div>

          <div class="flex items-center gap-3">
            @php
              $adminUser = auth('admin')->user();
              $roleLabel = ucfirst($adminUser?->role ?? 'user');
            @endphp
            <div class="w-9 h-9 rounded-full bg-primary text-white flex items-center justify-center text-xs font-bold">
              {{ strtoupper(substr($adminUser?->name ?? 'A', 0, 1)) }}
            </div>
            <div class="leading-tight">
              <div class="text-sm font-medium text-slate-900">{{ $adminUser?->name ?? 'Admin' }}</div>
              <div class="text-xs text-slate-500">{{ $roleLabel }}</div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <div class="px-3 sm:px-6 py-4 sm:py-6">
      <div class="max-w-7xl mx-auto">
        @yield('content')
      </div>
    </div>
  </main>

  @stack('modals')
  <x-flash-modal />

  @stack('scripts')
  @include('shared.partials.submit-loading')
</body>
</html>
