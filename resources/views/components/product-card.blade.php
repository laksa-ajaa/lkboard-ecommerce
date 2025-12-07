@props(['product'])

@php
    $productParam = $product->slug ?? ($product->id ?? null);
@endphp

<a href="{{ $productParam ? route('products.show', $productParam) : '#' }}"
    class="group flex flex-col rounded-2xl border border-slate-200 bg-white overflow-hidden hover:-translate-y-1 hover:border-indigo-200 hover:shadow-[0_18px_55px_rgba(15,23,42,0.18)] transition-all duration-300 cursor-pointer">
    <div class="relative block aspect-[4/3] bg-slate-900 overflow-hidden">
        @if (!empty($product->image_url))
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                class="h-full w-full object-cover object-center group-hover:scale-105 transition-transform duration-500">
        @else
            <div class="h-full w-full flex items-center justify-center text-xs text-slate-500">
                Keyboard image
            </div>
        @endif

        @if (!empty($product->badge))
            <div class="absolute left-3 top-3 flex gap-1">
                <x-badge variant="primary" solid>
                    {{ $product->badge }}
                </x-badge>
                @if (!empty($product->discount))
                    <x-badge variant="danger" solid>
                        -{{ $product->discount }}%
                    </x-badge>
                @endif
            </div>
        @endif

        @if (!empty($product->stock))
            <div class="absolute right-3 bottom-3">
                <span
                    class="rounded-full bg-slate-950/70 px-2 py-1 text-[10px] text-slate-100 border border-slate-700/70 backdrop-blur">
                    Stok: {{ $product->stock }}
                </span>
            </div>
        @endif
    </div>

    <div class="flex flex-1 flex-col px-3 pt-3 pb-3">
        <h3 class="text-sm font-semibold text-slate-900 line-clamp-2 tracking-tight">
            {{ $product->name }}
        </h3>
        @if (!empty($product->category?->name))
            <p class="mt-0.5 text-[11px] text-slate-500">
                {{ $product->category->name }}
            </p>
        @endif

        @if (!empty($product->switch_type))
            <p class="mt-1 text-[11px] text-slate-500">
                {{ $product->switch_type }}
            </p>
        @endif

        <div class="mt-3">
            <p class="text-sm font-semibold text-indigo-600">
                Rp {{ number_format($product->price, 0, ',', '.') }}
            </p>
            @if (!empty($product->original_price) && $product->original_price > $product->price)
                <p class="text-[11px] text-slate-500 line-through">
                    Rp {{ number_format($product->original_price, 0, ',', '.') }}
                </p>
            @endif
        </div>
    </div>
</a>
