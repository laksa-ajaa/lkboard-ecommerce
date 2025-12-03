@props(['name', 'role' => null, 'avatar' => null])

<figure class="rounded-2xl border border-slate-800 bg-slate-950/80 p-4 sm:p-5 shadow-[0_14px_40px_rgba(15,23,42,0.8)]">
    <div class="flex items-center gap-3">
        <div
            class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-500 text-[11px] font-semibold text-white">
            @if ($avatar)
                <img src="{{ $avatar }}" alt="{{ $name }}" class="h-full w-full rounded-full object-cover">
            @else
                {{ strtoupper(substr($name, 0, 2)) }}
            @endif
        </div>
        <div class="flex flex-col">
            <figcaption class="text-xs font-semibold text-slate-100">
                {{ $name }}
            </figcaption>
            @if ($role)
                <p class="text-[11px] text-slate-500">
                    {{ $role }}
                </p>
            @endif
        </div>
    </div>

    <blockquote class="mt-3 text-[11px] text-slate-300 leading-relaxed">
        {{ $slot }}
    </blockquote>
</figure>
