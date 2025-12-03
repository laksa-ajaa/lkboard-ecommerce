@props([
    'products' => collect(),
])

<div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
    @forelse($products as $product)
        <x-product-card :product="$product" />
    @empty
        <p class="col-span-full text-center text-sm text-gray-500 py-8">
            Belum ada produk yang dapat ditampilkan.
        </p>
    @endforelse
</div>
