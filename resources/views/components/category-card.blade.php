@props(['category'])

@php
    $categoryParam = $category->slug ?? ($category->id ?? null);
@endphp

<a href="{{ $categoryParam ? route('products.category', $categoryParam) : '#' }}"
    class="flex items-center gap-3 rounded-xl border border-gray-100 bg-white px-3 py-2.5 hover:border-indigo-200 hover:bg-indigo-50/40 transition-colors">
    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-xs text-indigo-600">
        {{ strtoupper(substr($category->name, 0, 2)) }}
    </div>
    <div class="flex-1 min-w-0">
        <p class="text-xs font-medium text-gray-900 truncate">
            {{ $category->name }}
        </p>
        @if (!empty($category->products_count))
            <p class="text-[10px] text-gray-400">
                {{ $category->products_count }} produk
            </p>
        @endif
    </div>
</a>
