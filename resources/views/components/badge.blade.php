@props([
    'variant' => 'primary', // primary, success, warning, danger, neutral
])

@php
    $base = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-semibold';

    $variants = [
        'primary' => 'bg-indigo-500/10 text-indigo-300 border border-indigo-400/40',
        'success' => 'bg-emerald-500/10 text-emerald-300 border border-emerald-400/40',
        'warning' => 'bg-amber-500/10 text-amber-300 border border-amber-400/40',
        'danger' => 'bg-rose-500/10 text-rose-300 border border-rose-400/40',
        'neutral' => 'bg-slate-500/10 text-slate-300 border border-slate-400/40',
    ];

    $classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

<span {{ $attributes->class($classes) }}>
    {{ $slot }}
</span>


