<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Kelola Akun — {{ config('app.name') }}</title>
  <link rel="icon" href="/favicon.png" type="image/png">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{primary:'#0f4c9a',blue:{600:'#0f4c9a',700:'#0a3d80'}}}}}</script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <style>*,*::before,*::after{box-sizing:border-box}html,body{margin:0;padding:0;height:100%;overflow:hidden}body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }</style>
</head>
<body class="h-screen overflow-hidden bg-slate-100 text-slate-800">
  <x-splash-screen :user-name="auth('admin')->user()?->name" :no-reload="true" />
  @include('admin.partials.sidebar')

  <main class="fixed top-0 right-0 bottom-0 z-10 overflow-y-auto">
    <header class="sticky top-0 z-10 bg-white/90 backdrop-blur border-b border-slate-200">
      <div class="px-6 py-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
          <h1 class="text-lg md:text-xl font-semibold text-slate-900">Kelola Akun</h1>
          <div class="flex items-center gap-3">
            @php $adminUser = auth('admin')->user(); @endphp
            <div class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold">
              {{ strtoupper(substr($adminUser?->name ?? 'A', 0, 1)) }}
            </div>
            <div class="leading-tight">
              <div class="text-sm font-medium text-slate-900">{{ $adminUser?->name ?? 'Admin' }}</div>
              <div class="text-xs text-slate-500">Admin</div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <div class="px-3 sm:px-6 py-4 sm:py-6">
      <div class="max-w-7xl mx-auto space-y-4">
        @php $activeTab = request('tab', 'bk'); @endphp

        {{-- Tabs --}}
        <div class="flex items-center gap-1 bg-white border border-slate-200 rounded-xl p-1 w-fit">
          <a href="{{ route('admin.accounts.index', ['tab' => 'bk']) }}"
             class="tab-btn px-5 py-2 rounded-lg text-sm font-medium transition
                    {{ $activeTab === 'bk' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
            <i class="fa-solid fa-chalkboard-teacher mr-1.5 text-xs"></i> Guru BK
            <span class="ml-1.5 text-xs font-bold px-2 py-0.5 rounded-full
                         {{ $activeTab === 'bk' ? 'bg-white/25 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $bkAccounts->total() }}</span>
          </a>
          <a href="{{ route('admin.accounts.index', ['tab' => 'siswa']) }}"
             class="tab-btn px-5 py-2 rounded-lg text-sm font-medium transition
                    {{ $activeTab === 'siswa' ? 'bg-blue-600 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
            <i class="fa-solid fa-user-graduate mr-1.5 text-xs"></i> Siswa/i
            <span class="ml-1.5 text-xs font-bold px-2 py-0.5 rounded-full
                         {{ $activeTab === 'siswa' ? 'bg-white/25 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $siswaAccounts->total() }}</span>
          </a>
        </div>

        {{-- ── BK Tab ── --}}
        <div id="panelBk" class="bg-white rounded-xl border border-slate-200 {{ $activeTab !== 'bk' ? 'hidden' : '' }}">
          <div class="px-6 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
              <h3 class="font-bold text-slate-900 uppercase tracking-wide text-sm">Akun Guru BK</h3>
              <p class="text-xs text-slate-500 mt-0.5">NIP 18 digit. BK dapat memiliki banyak siswa dan kelas.</p>
            </div>
            <div class="flex gap-2 flex-wrap">
              <button type="button" id="openImportBkModal"
                class="inline-flex items-center gap-2 bg-emerald-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-emerald-600/90 transition">
                <i class="fa-solid fa-file-arrow-up text-xs"></i> Import Excel
              </button>
              <button type="button" id="openCreateBkModal"
                class="inline-flex items-center gap-2 bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                <i class="fa-solid fa-plus text-xs"></i> Tambah BK
              </button>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="text-center text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                  <th class="px-4 py-3 font-medium">No</th>
                  <th class="px-4 py-3 font-medium text-left">Nama</th>
                  <th class="px-4 py-3 font-medium">Email</th>
                  <th class="px-4 py-3 font-medium">NIP</th>
                  <th class="px-4 py-3 font-medium">Kelas</th>
                  <th class="px-4 py-3 font-medium">Siswa</th>
                  <th class="px-4 py-3 font-medium">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                @forelse($bkAccounts as $bk)
                <tr class="hover:bg-slate-50 transition text-center">
                  <td class="px-4 py-3 text-slate-400 text-xs">{{ ($bkAccounts->currentPage()-1)*$bkAccounts->perPage()+$loop->iteration }}</td>
                  <td class="px-4 py-3 font-medium text-slate-800 text-left">{{ $bk->name }}</td>
                  <td class="px-4 py-3 text-slate-500 text-xs font-mono">{{ $bk->email ?? '—' }}</td>
                  <td class="px-4 py-3 text-slate-600 font-mono text-xs">{{ $bk->login_id }}</td>
                  <td class="px-4 py-3 text-slate-700 text-xs font-semibold">{{ $bk->classrooms_count }}</td>
                  <td class="px-4 py-3 text-slate-700 text-xs font-semibold">{{ $bk->siswa_count }}</td>
                  <td class="px-4 py-3">
                    <button type="button" class="bk-action-btn inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition"
                      data-id="{{ $bk->id }}" data-name="{{ $bk->name }}" data-email="{{ $bk->email }}" data-login-id="{{ $bk->login_id }}">
                      <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                  </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-8 text-center text-slate-400">Belum ada akun BK.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
          <div class="px-4 py-3 border-t border-slate-100">{{ $bkAccounts->links('components.pagination.default', ['pageParam' => 'bk_page', 'windowParam' => 'bk_pw']) }}</div>
        </div>

        {{-- ── Siswa Tab (hidden by default) ── --}}
        <div id="panelSiswa" class="bg-white rounded-xl border border-slate-200 {{ $activeTab !== 'siswa' ? 'hidden' : '' }}">
          <div class="px-6 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
              <h3 class="font-bold text-slate-900 uppercase tracking-wide text-sm">Akun Siswa/i</h3>
              <p class="text-xs text-slate-500 mt-0.5">NIS maks 10 digit. Kelas menentukan BK pembimbing otomatis.</p>
            </div>
            <div class="flex gap-2 flex-wrap">
              <button type="button" id="openImportSiswaModal"
                class="inline-flex items-center gap-2 bg-emerald-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-emerald-600/90 transition">
                <i class="fa-solid fa-file-arrow-up text-xs"></i> Import Excel
              </button>
              <button type="button" id="openCreateSiswaModal"
                class="inline-flex items-center gap-2 bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                <i class="fa-solid fa-plus text-xs"></i> Tambah Siswa
              </button>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="text-center text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                  <th class="px-4 py-3 font-medium">No</th>
                  <th class="px-4 py-3 font-medium text-left">Nama</th>
                  <th class="px-4 py-3 font-medium">L/P</th>
                  <th class="px-4 py-3 font-medium">NIS</th>
                  <th class="px-4 py-3 font-medium">Kelas</th>
                  <th class="px-4 py-3 font-medium">Pembimbing</th>
                  <th class="px-4 py-3 font-medium">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                @forelse($siswaAccounts as $s)
                <tr class="hover:bg-slate-50 transition text-center">
                  <td class="px-4 py-3 text-slate-400 text-xs">{{ ($siswaAccounts->currentPage()-1)*$siswaAccounts->perPage()+$loop->iteration }}</td>
                  <td class="px-4 py-3 font-medium text-slate-800 text-left">{{ $s->name }}</td>
                  <td class="px-4 py-3 text-slate-700 text-xs font-semibold">{{ $s->jenis_kelamin ?? '—' }}</td>
                  <td class="px-4 py-3 text-slate-600 font-mono text-xs">{{ $s->login_id }}</td>
                  <td class="px-4 py-3">
                    @if($s->classroom)
                      <span class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full">
                        <i class="fa-solid fa-school text-[10px]"></i> {{ $s->classroom->name }}
                      </span>
                    @else
                      <span class="text-xs text-slate-400 italic">—</span>
                    @endif
                  </td>
                  <td class="px-4 py-3">
                    @if($s->bk)
                      <span class="text-xs text-slate-700">{{ $s->bk->name }}</span>
                    @else
                      <span class="text-xs text-slate-400 italic">—</span>
                    @endif
                  </td>
                  <td class="px-4 py-3">
                    <button type="button" class="siswa-action-btn inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition"
                      data-id="{{ $s->id }}" data-name="{{ $s->name }}" data-email="{{ $s->email }}" data-login-id="{{ $s->login_id }}" data-classroom-id="{{ $s->classroom_id }}" data-gender="{{ $s->jenis_kelamin }}">
                      <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                  </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-8 text-center text-slate-400">Belum ada akun Siswa/i.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
          <div class="px-4 py-3 border-t border-slate-100">{{ $siswaAccounts->links('components.pagination.default', ['pageParam' => 'siswa_page', 'windowParam' => 'siswa_pw']) }}</div>
        </div>

      </div>
    </div>
  </main>

  {{-- ── Dropdown Portal ── --}}
  <div id="actionMenuPortal" class="fixed z-[9999] hidden">
    <div class="w-48 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden">
      <button type="button" id="portalEditBtn"   class="w-full px-4 py-2.5 text-left text-sm text-slate-700 hover:bg-slate-50 transition flex items-center gap-2"><i class="fa-solid fa-pen-to-square w-4 text-center"></i> Edit</button>
      <button type="button" id="portalDeleteBtn" class="w-full px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50 transition flex items-center gap-2"><i class="fa-solid fa-trash-can w-4 text-center"></i> Hapus</button>
    </div>
  </div>

  {{-- ── BK CRUD Modal ── --}}
  <div id="bkModal" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-black/40" data-close="bkModal"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-lg bg-white rounded-2xl border border-slate-200 shadow-xl">
        <div class="px-6 py-4 border-b border-slate-200">
          <div class="text-xs text-slate-500">Akun Guru BK</div>
          <div id="bkModalTitle" class="text-lg font-semibold text-slate-900">Tambah BK</div>
        </div>
        <form id="bkForm" method="POST" action="{{ route('admin.accounts.bk.store') }}" class="p-6 space-y-4" novalidate>
          @csrf
          <input id="bkMethodField" type="hidden" name="_method" value="PUT" disabled>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-slate-700 mb-1">Nama</label>
              <input id="bkName" name="name" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-600" required>
              @error('name')<div class="text-xs text-red-500 mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-slate-700 mb-1">Email <span class="text-slate-400 text-xs">(opsional, @gmail.com)</span></label>
              <input id="bkEmail" name="email" type="email" placeholder="nama@gmail.com" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-600">
              @error('email')<div class="text-xs text-red-500 mt-1">{{ $message }}</div>@enderror
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">NIP <span class="text-slate-400 text-xs">(18 digit)</span></label>
              <input id="bkLoginId" name="login_id" inputmode="numeric" maxlength="18" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-600 font-mono" required>
              @error('login_id')<div class="text-xs text-red-500 mt-1">{{ $message }}</div>@enderror
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Password <span id="bkPasswordNote" class="text-slate-400 text-xs">(min 6, maks 12)</span></label>
              <input id="bkPassword" name="password" type="password" maxlength="12" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-600">
              @error('password')<div class="text-xs text-red-500 mt-1">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="flex items-center justify-end gap-2 pt-1">
            <button type="button" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition" data-close="bkModal">Batal</button>
            <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700 transition">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ── Siswa CRUD Modal ── --}}
  <div id="siswaModal" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-black/40" data-close="siswaModal"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-lg bg-white rounded-2xl border border-slate-200 shadow-xl">
        <div class="px-6 py-4 border-b border-slate-200">
          <div class="text-xs text-slate-500">Akun Siswa/i</div>
          <div id="siswaModalTitle" class="text-lg font-semibold text-slate-900">Tambah Siswa</div>
        </div>
        <form id="siswaForm" method="POST" action="{{ route('admin.accounts.siswa.store') }}" class="p-6 space-y-4" novalidate>
          @csrf
          <input id="siswaMethodField" type="hidden" name="_method" value="PUT" disabled>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-slate-700 mb-1">Nama</label>
              <input id="siswaName" name="name" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-600" required>
              @error('name')<div class="text-xs text-red-500 mt-1">{{ $message }}</div>@enderror
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Jenis Kelamin <span class="text-red-400">*</span></label>
              <select id="siswaGender" name="jenis_kelamin" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                <option value="">— Pilih —</option>
                <option value="L">Laki-laki (L)</option>
                <option value="P">Perempuan (P)</option>
              </select>
              @error('jenis_kelamin')<div class="text-xs text-red-500 mt-1">{{ $message }}</div>@enderror
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">NIS <span class="text-slate-400 text-xs">(maks 10 digit)</span></label>
              <input id="siswaLoginId" name="login_id" inputmode="numeric" maxlength="10" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-600 font-mono" required>
              @error('login_id')<div class="text-xs text-red-500 mt-1">{{ $message }}</div>@enderror
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Password <span class="text-slate-400 text-xs">(min 6, maks 12)</span></label>
              <input id="siswaPassword" name="password" type="password" maxlength="12" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-600">
              @error('password')<div class="text-xs text-red-500 mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-slate-700 mb-1">Email <span class="text-slate-400 text-xs">(opsional, @gmail.com)</span></label>
              <input id="siswaEmail" name="email" type="email" placeholder="nama@gmail.com" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-600">
              @error('email')<div class="text-xs text-red-500 mt-1">{{ $message }}</div>@enderror
            </div>
            {{-- hidden: holds resolved classroom_id --}}
            <input type="hidden" id="siswaClassroomId" name="classroom_id">
            @php
              $_uniqueKelas   = $allKelas->pluck('kelas')->unique()->sort()->values();
              $_uniqueJurusan = $allKelas->pluck('jurusan')->unique()->sort()->values();
            @endphp
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Kelas <span class="text-slate-400 text-xs">(opsional)</span></label>
              <select id="siswaKelasSelect" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-600">
                <option value="">— Pilih kelas —</option>
                @foreach($_uniqueKelas as $k)
                  <option value="{{ $k }}">{{ $k }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Jurusan <span class="text-slate-400 text-xs">(opsional)</span></label>
              <select id="siswaJurusanSelect" class="w-full border border-slate-300 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-600">
                <option value="">— Pilih jurusan —</option>
                @foreach($_uniqueJurusan as $j)
                  <option value="{{ $j }}">{{ $j }}</option>
                @endforeach
              </select>
              @error('classroom_id')<div class="text-xs text-red-500 mt-1">{{ $message }}</div>@enderror
              <p id="siswaKelasMatch" class="text-xs mt-1.5 hidden"></p>
            </div>
          </div>
          <div class="flex items-center justify-end gap-2 pt-1">
            <button type="button" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition" data-close="siswaModal">Batal</button>
            <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700 transition">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ── Delete Confirm Modal ── --}}
  <div id="deleteModal" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-black/40" data-close="deleteModal"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-sm bg-white rounded-2xl shadow-2xl p-8 text-center">
        <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-5">
          <i class="fa-solid fa-circle-exclamation text-2xl text-red-400"></i>
        </div>
        <div class="text-xl font-bold text-slate-900 mb-2">Hapus Akun?</div>
        <p class="text-sm text-slate-500 mb-1">Yakin ingin menghapus akun <span id="deleteNameSpan" class="font-semibold text-slate-700">—</span>?</p>
        <p class="text-xs text-slate-400 mb-6">Aksi ini tidak bisa dibatalkan.</p>
        <form id="deleteForm" method="POST" action="">
          @csrf @method('DELETE')
          <div class="flex gap-3">
            <button type="button" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition font-medium text-sm" data-close="deleteModal">Batal</button>
            <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-red-400 hover:bg-red-500 text-white transition font-medium text-sm">Hapus</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ── Import Form Modal ── --}}
  <div id="importFormModal" class="fixed inset-0 z-[9999] hidden">
    <div class="absolute inset-0 bg-black/40" data-close="importFormModal"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
      <div class="w-full max-w-md bg-white rounded-2xl border border-slate-200 shadow-xl">
        <div class="px-6 py-4 border-b border-slate-200">
          <div class="text-xs text-slate-500">Import Excel</div>
          <div id="importFormTitle" class="text-lg font-semibold text-slate-900">Import Akun</div>
        </div>
        <form id="importForm" method="POST" action="{{ route('admin.accounts.import') }}" enctype="multipart/form-data" class="p-6 space-y-4">
          @csrf
          <input type="hidden" id="importTypeInput" name="import_type" value="bk">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">File Excel / Spreadsheet</label>
            <div id="importDropZone" class="relative border-2 border-dashed border-slate-200 rounded-xl px-4 py-6 text-center cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 transition">
              <input type="file" id="importFileInput" name="file" accept=".xlsx,.xls,.csv,.ods" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required>
              <i id="importFileIcon" class="fa-solid fa-cloud-arrow-up text-2xl text-slate-300 mb-2 block"></i>
              <p id="importFileName" class="text-sm text-slate-500">Klik atau seret file di sini</p>
              <p id="importFileExt" class="text-xs text-slate-400 mt-1">.xlsx, .xls, .csv, .ods — maks 5MB</p>
            </div>
          </div>
          <div class="bg-slate-50 rounded-xl p-4 text-xs text-slate-500 space-y-1">
            <p class="font-semibold text-slate-600 mb-1"><i class="fa-solid fa-circle-info mr-1 text-blue-500"></i> Format kolom:</p>
            <div><span class="font-mono bg-white border border-slate-200 px-1.5 py-0.5 rounded text-xs">No.</span> — Opsional (boleh dikosongkan)</div>
            <div><span class="font-mono bg-white border border-slate-200 px-1.5 py-0.5 rounded text-xs">Nama Lengkap</span> — Wajib</div>
            <div id="importGenderHint" class="hidden"><span class="font-mono bg-white border border-slate-200 px-1.5 py-0.5 rounded text-xs">Jenis Kelamin (P/L)</span> — Wajib untuk siswa</div>
            <div id="importUsiaHint" class="hidden"><span class="font-mono bg-white border border-slate-200 px-1.5 py-0.5 rounded text-xs">Usia</span> — Opsional (diabaikan sistem)</div>
            <div id="importKelasHint"><span class="font-mono bg-white border border-slate-200 px-1.5 py-0.5 rounded text-xs">Kelas</span> — Siswa: wajib, BK: opsional (untuk sinkron BK pembimbing ke Data Kelas)</div>
            <div id="importIdHint"><span class="font-mono bg-white border border-slate-200 px-1.5 py-0.5 rounded text-xs">NIP</span> — Wajib untuk BK (9-18 digit)</div>
            <div id="importEmailHint"><span class="font-mono bg-white border border-slate-200 px-1.5 py-0.5 rounded text-xs">Email / Alamat Email</span> — Opsional (@gmail.com)</div>
            <div class="text-[11px] text-slate-400 mt-1">Untuk mencegah angka berubah jadi `1.98E+17`, set kolom NIP/NIS ke format Text di Excel.</div>
            <div class="pt-2">
              <a id="importSampleLink" href="/templates/import/contoh_import_guru_bk.csv" download
                 class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-700 font-semibold">
                <i class="fa-solid fa-download text-[11px]"></i>
                <span id="importSampleText">Download Contoh Guru BK</span>
              </a>
            </div>
          </div>
          <div class="flex items-center justify-end gap-2">
            <button type="button" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition text-sm" data-close="importFormModal">Batal</button>
            <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700 transition text-sm">
              <i class="fa-solid fa-file-arrow-up mr-1 text-xs"></i> Import
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <x-flash-modal />

  <script>
  (function () {
    // ── Route templates ──────────────────────────────────────────────
    const routes = {
      bkUpdate:    @json(route('admin.accounts.bk.update',    0)),
      bkDelete:    @json(route('admin.accounts.bk.delete',    0)),
      siswaUpdate: @json(route('admin.accounts.siswa.update', 0)),
      siswaDelete: @json(route('admin.accounts.siswa.delete', 0)),
      assignBk:    @json(route('admin.accounts.siswa.assignBk', 0)),
    };
    function fillId(tpl, id) { return String(tpl).replace(/\/0$/, '/' + id); }

    // ── Modal helpers ────────────────────────────────────────────────
    function openModal(id)  { document.getElementById(id)?.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }
    function closeModal(id) { document.getElementById(id)?.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }

    document.addEventListener('click', (e) => {
      const closeTarget = e.target.closest('[data-close]');
      if (closeTarget) { closeModal(closeTarget.dataset.close); return; }
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') ['bkModal','siswaModal','deleteModal','importFormModal'].forEach(closeModal);
    });

    // ── Tabs (panel toggle for validation-error redirect only) ─────
    const panelBk    = document.getElementById('panelBk');
    const panelSiswa = document.getElementById('panelSiswa');

    function activateTab(tab) {
      panelBk.classList.toggle('hidden', tab !== 'bk');
      panelSiswa.classList.toggle('hidden', tab !== 'siswa');
    }

    // ── BK CRUD ──────────────────────────────────────────────────────
    const bkForm = document.getElementById('bkForm');

    function openCreateBk() {
      document.getElementById('bkModalTitle').textContent = 'Tambah Guru BK';
      bkForm.action = @json(route('admin.accounts.bk.store'));
      document.getElementById('bkMethodField').disabled = true;
      document.getElementById('bkName').value = '';
      document.getElementById('bkEmail').value = '';
      document.getElementById('bkLoginId').value = '';
      document.getElementById('bkPassword').value = '';
      document.getElementById('bkPassword').required = true;
      document.getElementById('bkPasswordNote').textContent = '(min 6, maks 12)';
      openModal('bkModal');
    }
    function openEditBk({ id, name, email, loginId }) {
      document.getElementById('bkModalTitle').textContent = 'Edit Guru BK';
      bkForm.action = fillId(routes.bkUpdate, id);
      document.getElementById('bkMethodField').disabled = false;
      document.getElementById('bkName').value = name || '';
      document.getElementById('bkEmail').value = email || '';
      document.getElementById('bkLoginId').value = loginId || '';
      document.getElementById('bkPassword').value = '';
      document.getElementById('bkPassword').required = false;
      document.getElementById('bkPasswordNote').textContent = '(opsional — kosongkan jika tidak diubah)';
      openModal('bkModal');
    }

    document.getElementById('openCreateBkModal')?.addEventListener('click', openCreateBk);

    // input: digits only for NIP
    document.getElementById('bkLoginId')?.addEventListener('input', (e) => {
      e.target.value = e.target.value.replace(/\D/g, '').slice(0, 18);
    });

    // ── Siswa CRUD ───────────────────────────────────────────────────
    const siswaForm = document.getElementById('siswaForm');

    // ── Kelas / Classroom lookup ─────────────────────────────────────
    @php
      $_kelasJson      = $allKelas->map(fn($k) => ['id' => $k->id, 'kelas' => $k->kelas, 'jurusan' => $k->jurusan, 'bk' => $k->bk?->name ?? null]);
      $_classroomsJson = $allClassrooms->map(fn($c) => ['id' => $c->id, 'classId' => $c->class_id]);
    @endphp
    const _kelas      = @json($_kelasJson);
    const _classrooms = @json($_classroomsJson);

    function matchClassroomId(kelas, jurusan) {
      if (!kelas || !jurusan) return '';
      const k  = _kelas.find(x => x.kelas === kelas && x.jurusan === jurusan);
      if (!k) return '';
      const cr = _classrooms.find(c => c.classId === k.id);
      return cr ? cr.id : '';
    }
    function getKelasFromClassroomId(classroomId) {
      if (!classroomId) return null;
      const cr = _classrooms.find(c => c.id === classroomId);
      if (!cr) return null;
      return _kelas.find(k => k.id === cr.classId) ?? null;
    }
    function updateKelasHint() {
      const kVal = document.getElementById('siswaKelasSelect')?.value;
      const jVal = document.getElementById('siswaJurusanSelect')?.value;
      const id   = matchClassroomId(kVal, jVal);
      document.getElementById('siswaClassroomId').value = id;
      const hint = document.getElementById('siswaKelasMatch');
      if (!hint) return;
      if (id) {
        const kObj = _kelas.find(x => x.kelas === kVal && x.jurusan === jVal);
        hint.textContent = 'BK: ' + (kObj?.bk ?? '—');
        hint.className = 'text-xs text-emerald-600 mt-1.5';
      } else if (kVal && jVal) {
        hint.textContent = 'Kombinasi kelas tidak ditemukan';
        hint.className = 'text-xs text-red-500 mt-1.5';
      } else {
        hint.textContent = '';
        hint.className = 'text-xs mt-1.5 hidden';
      }
    }
    document.getElementById('siswaKelasSelect')?.addEventListener('change', updateKelasHint);
    document.getElementById('siswaJurusanSelect')?.addEventListener('change', updateKelasHint);

    function openCreateSiswa() {
      document.getElementById('siswaModalTitle').textContent = 'Tambah Siswa/i';
      siswaForm.action = @json(route('admin.accounts.siswa.store'));
      document.getElementById('siswaMethodField').disabled = true;
      document.getElementById('siswaName').value = '';
      document.getElementById('siswaGender').value = '';
      document.getElementById('siswaEmail').value = '';
      document.getElementById('siswaLoginId').value = '';
      document.getElementById('siswaPassword').value = '';
      document.getElementById('siswaPassword').required = true;
      document.getElementById('siswaClassroomId').value = '';
      document.getElementById('siswaKelasSelect').value = '';
      document.getElementById('siswaJurusanSelect').value = '';
      const hint = document.getElementById('siswaKelasMatch');
      if (hint) { hint.textContent = ''; hint.className = 'text-xs mt-1.5 hidden'; }
      openModal('siswaModal');
    }
    function openEditSiswa({ id, name, email, loginId, classroomId, gender }) {
      document.getElementById('siswaModalTitle').textContent = 'Edit Siswa/i';
      siswaForm.action = fillId(routes.siswaUpdate, id);
      document.getElementById('siswaMethodField').disabled = false;
      document.getElementById('siswaName').value = name || '';
      document.getElementById('siswaGender').value = gender || '';
      document.getElementById('siswaEmail').value = email || '';
      document.getElementById('siswaLoginId').value = loginId || '';
      document.getElementById('siswaPassword').value = '';
      document.getElementById('siswaPassword').required = false;
      document.getElementById('siswaClassroomId').value = classroomId || '';
      const kelasMatch = classroomId ? getKelasFromClassroomId(classroomId) : null;
      document.getElementById('siswaKelasSelect').value   = kelasMatch?.kelas   ?? '';
      document.getElementById('siswaJurusanSelect').value = kelasMatch?.jurusan ?? '';
      updateKelasHint();
      openModal('siswaModal');
    }

    document.getElementById('openCreateSiswaModal')?.addEventListener('click', () => { activateTab('siswa'); openCreateSiswa(); });

    document.getElementById('siswaLoginId')?.addEventListener('input', (e) => {
      e.target.value = e.target.value.replace(/\D/g, '').slice(0, 10);
    });

    // ── Delete ───────────────────────────────────────────────────────
    function openDelete({ id, name, type }) {
      document.getElementById('deleteNameSpan').textContent = name || '—';
      document.getElementById('deleteForm').action = type === 'bk' ? fillId(routes.bkDelete, id) : fillId(routes.siswaDelete, id);
      openModal('deleteModal');
    }

    // ── Action menus ─────────────────────────────────────────────────
    const portal  = document.getElementById('actionMenuPortal');
    const editBtn = document.getElementById('portalEditBtn');
    const delBtn  = document.getElementById('portalDeleteBtn');
    let _active = null;

    function positionPortal(anchor) {
      portal.style.visibility = 'hidden';
      portal.classList.remove('hidden');
      const b  = anchor.getBoundingClientRect();
      const pw = portal.offsetWidth;
      const ph = portal.offsetHeight;
      let left = b.right - pw;
      let top  = b.bottom + 4;
      if (top + ph > window.innerHeight - 8) top = b.top - ph - 4;
      left = Math.max(8, Math.min(left, window.innerWidth - pw - 8));
      portal.style.left = left + 'px';
      portal.style.top  = top + 'px';
      portal.style.visibility = '';
    }
    function closePortal() { portal?.classList.add('hidden'); _active = null; }

    document.addEventListener('click', (e) => {
      const btn = e.target.closest('.bk-action-btn, .siswa-action-btn');
      if (btn) {
        const isSame = _active && _active.id === btn.dataset.id && _active.type === (btn.classList.contains('bk-action-btn') ? 'bk' : 'siswa');
        if (isSame) { closePortal(); return; }
        _active = {
          id: btn.dataset.id, name: btn.dataset.name,
          email: btn.dataset.email, loginId: btn.dataset.loginId,
          classroomId: btn.dataset.classroomId, gender: btn.dataset.gender,
          type: btn.classList.contains('bk-action-btn') ? 'bk' : 'siswa',
        };
        positionPortal(btn);
        return;
      }
      if (!e.target.closest('#actionMenuPortal')) closePortal();
    });

    editBtn?.addEventListener('click', () => {
      if (!_active) return;
      const a = _active; closePortal();
      if (a.type === 'bk') openEditBk(a);
      else openEditSiswa(a);
    });
    delBtn?.addEventListener('click', () => {
      if (!_active) return;
      const a = _active; closePortal();
      openDelete(a);
    });

    window.addEventListener('resize', closePortal);
    document.addEventListener('scroll', closePortal, true);

    // ── Import flow ──────────────────────────────────────────────────
    document.getElementById('openImportBkModal')?.addEventListener('click', () => openImportForm('bk'));
    document.getElementById('openImportSiswaModal')?.addEventListener('click', () => openImportForm('siswa'));

    function openImportForm(type) {
      const isBk = type === 'bk';
      const downloadRoutes = {
        bk: @json(route('admin.accounts.import.template', ['type' => 'bk'])),
        siswa: @json(route('admin.accounts.import.template', ['type' => 'siswa'])),
      };
      const sampleLink = document.getElementById('importSampleLink');
      const sampleText = document.getElementById('importSampleText');
      const genderHint = document.getElementById('importGenderHint');
      const usiaHint = document.getElementById('importUsiaHint');
      const kelasHint = document.getElementById('importKelasHint');
      const emailHint = document.getElementById('importEmailHint');
      document.getElementById('importTypeInput').value = type;
      document.getElementById('importFormTitle').textContent = isBk ? 'Import Guru BK' : 'Import Siswa/i';
      document.getElementById('importIdHint').innerHTML = isBk
        ? '<span class="font-mono bg-white border border-slate-200 px-1.5 py-0.5 rounded">NIP</span> — Wajib untuk BK (9-18 digit), jadi password awal'
        : '<span class="font-mono bg-white border border-slate-200 px-1.5 py-0.5 rounded">NIS</span> — Wajib untuk siswa (maks 10 digit), jadi password awal';
      if (genderHint) genderHint.classList.toggle('hidden', isBk);
      if (usiaHint) usiaHint.classList.toggle('hidden', isBk);
      if (kelasHint) {
        kelasHint.classList.remove('hidden');
        kelasHint.innerHTML = isBk
          ? '<span class="font-mono bg-white border border-slate-200 px-1.5 py-0.5 rounded text-xs">Kelas</span> — Opsional untuk BK. Jika diisi (contoh: XII AKL 1), sistem sinkron ke Data Kelas.'
          : '<span class="font-mono bg-white border border-slate-200 px-1.5 py-0.5 rounded text-xs">Kelas</span> — Wajib untuk siswa (contoh: XII AKL 1 / XII PPLG), otomatis sinkron ke Data Kelas.';
      }
      if (emailHint) emailHint.classList.toggle('hidden', !isBk);
      if (sampleLink && sampleText) {
        sampleLink.href = isBk ? downloadRoutes.bk : downloadRoutes.siswa;
        sampleText.textContent = isBk ? 'Download Contoh Guru BK' : 'Download Contoh Siswa/i';
      }
      const fi = document.getElementById('importFileInput');
      const fn = document.getElementById('importFileName');
      if (fi) fi.value = '';
      if (fn) { fn.textContent = 'Klik atau seret file di sini'; fn.className = 'text-sm text-slate-500'; }
      const icon = document.getElementById('importFileIcon');
      if (icon) icon.className = 'fa-solid fa-cloud-arrow-up text-2xl text-slate-300 mb-2 block';
      openModal('importFormModal');
    }

    document.getElementById('importFileInput')?.addEventListener('change', (e) => {
      const f = e.target.files[0];
      const fn = document.getElementById('importFileName');
      const icon = document.getElementById('importFileIcon');
      const zone = document.getElementById('importDropZone');
      const ext  = document.getElementById('importFileExt');
      if (f) {
        fn.textContent = f.name; fn.className = 'text-sm font-semibold text-blue-700';
        icon.className = 'fa-solid fa-file-excel text-2xl text-emerald-500 mb-2 block';
        zone.classList.replace('border-slate-200','border-blue-500');
        if (ext) { ext.textContent = (f.size/1024).toFixed(0) + ' KB'; }
      } else {
        fn.textContent = 'Klik atau seret file di sini'; fn.className = 'text-sm text-slate-500';
        icon.className = 'fa-solid fa-cloud-arrow-up text-2xl text-slate-300 mb-2 block';
        zone.classList.replace('border-blue-500','border-slate-200');
        if (ext) ext.textContent = '.xlsx, .xls, .csv, .ods — maks 5MB';
      }
    });

    // ── Re-open panel on validation error ───────────────────────────
    @if($errors->any())
      activateTab(@json(session('_flash_tab', 'bk')));
    @endif
  })();
  </script>
</body>
</html>
