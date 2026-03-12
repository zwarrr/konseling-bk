{{--
    Drop-action: Hapus item.
    Dispatches 'bk-delete-confirm' custom event yang ditangani oleh JS di halaman.

    Usage (di dalam [data-drop] inline dropdown):
        <x-drop-action.delete
            :title="$item->title"
            :action="route('admin.landing.serviceDestroy', $item)"
        />

    Pastikan halaman memiliki listener event:
        window.addEventListener('bk-delete-confirm', e => {
            const { title, action } = e.detail;
            document.getElementById('deleteItemName').textContent = title;
            document.getElementById('deleteForm').action = action;
            document.getElementById('deleteModal').classList.remove('hidden');
        });

    Props:
        - title  : Nama item yang ditampilkan di modal konfirmasi
        - action : URL DELETE form action
        - label  : Teks tombol (default: 'Hapus')
--}}

@props([
    'title',
    'action',
    'label' => 'Hapus',
])

<div class="border-t border-gray-100 my-1"></div>
<button type="button"
    class="w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2 transition"
    onclick="window.dispatchEvent(new CustomEvent('bk-delete-confirm', { detail: { title: {{ \Illuminate\Support\Js::from($title) }}, action: {{ \Illuminate\Support\Js::from($action) }} } })); document.querySelectorAll('[data-drop-menu]').forEach(function(m){ m.classList.add('hidden') })">
    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
    </svg>
    {{ $label }}
</button>
