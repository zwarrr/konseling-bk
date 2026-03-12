{{--
    Drop-action: Toggle status Publish / Draft.
    Mengirim PATCH form langsung tanpa modal konfirmasi (reversible action).

    Usage (di dalam [data-drop] inline dropdown):
        <x-drop-action.toggle-publish
            :current-status="$item->is_active ? 'publish' : 'draft'"
            :toggle-url="route('admin.landing.serviceToggle', $item)"
        />

    Props:
        - currentStatus : 'publish' | 'draft'  (status saat ini)
        - toggleUrl     : URL PATCH route untuk toggle status
--}}

@props([
    'currentStatus' => 'draft',
    'toggleUrl',
])

@php
    $isPublished = $currentStatus === 'publish';
@endphp

<form method="POST" action="{{ $toggleUrl }}">
    @csrf
    @method('PATCH')
    @if($isPublished)
        <button type="submit"
            class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2 transition"
            onclick="document.querySelectorAll('[data-drop-menu]').forEach(function(m){ m.classList.add('hidden') })">
            <svg class="w-4 h-4 text-orange-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v8" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 8a8 8 0 1 1-12 0" />
            </svg>
            Set Draf
        </button>
    @else
        <button type="submit"
            class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2 transition"
            onclick="document.querySelectorAll('[data-drop-menu]').forEach(function(m){ m.classList.add('hidden') })">
            <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v8" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 8a8 8 0 1 1-12 0" />
            </svg>
            Set Publish
        </button>
    @endif
</form>
