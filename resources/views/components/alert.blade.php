@php
    $status = session('status');
    $error = session('error') ?? ($errors->any() ? 'Terjadi kesalahan. Silakan cek kembali data yang diisi.' : null);
@endphp

@if($status || $error)
    <div x-data="{ open: true }" x-cloak x-init="setTimeout(() => open = false, 5000)" x-show="open" x-transition
        class="fixed bottom-6 right-6 z-50 max-w-sm rounded-2xl px-4 py-3 text-sm flex items-start gap-3 shadow-xl
            {{ $error ? 'border border-red-200 bg-red-50/95 text-red-800' : 'border border-emerald-200 bg-emerald-50/95 text-emerald-900' }}">
        <div class="mt-0.5 text-base">
            {{ $error ? '⚠️' : '✅' }}
        </div>
        <div class="flex-1 space-y-1">
            @if($status)
                <p class="font-medium">{{ $status }}</p>
            @endif

            @if($error)
                <p>{{ $error }}</p>
                @if($errors->any())
                    <ul class="list-disc pl-4 space-y-0.5 mt-1">
                        @foreach($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                @endif
            @endif
        </div>
        <button type="button" class="ml-2 text-[11px] text-gray-400 hover:text-gray-600" @click="open = false">
            ✕
        </button>
    </div>
@endif
