@props(['label', 'icon' => null])

<div
    class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-slate-200 bg-white shadow-sm 
    hover:bg-slate-50 cursor-pointer transition text-sm font-semibold text-slate-800 whitespace-nowrap">

    <span>{{ $label }}</span>
</div>
