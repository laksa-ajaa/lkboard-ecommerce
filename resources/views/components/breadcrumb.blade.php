@php
    $items = $items ?? [
        ['label' => 'Beranda', 'href' => url('/')],
    ];
@endphp

<nav class="flex items-center text-[11px] text-gray-500" aria-label="Breadcrumb">
    <ol class="inline-flex items-center gap-1">
        @foreach($items as $index => $item)
            <li class="inline-flex items-center gap-1">
                @if($index > 0)
                    <span class="text-gray-300">/</span>
                @endif

                @if(!empty($item['href']) && $index !== count($items) - 1)
                    <a href="{{ $item['href'] }}" class="hover:text-indigo-600">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="text-gray-900 font-medium">
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
