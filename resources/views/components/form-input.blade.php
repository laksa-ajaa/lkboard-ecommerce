@props([
    'label' => null,
    'name',
    'type' => 'text',
    'placeholder' => null,
    'required' => false,
])

<div class="space-y-1 text-sm">
    @if($label)
        <label for="{{ $name }}" class="block text-xs font-medium text-gray-700">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        {{ $required ? 'required' : '' }}
        placeholder="{{ $placeholder }}"
        {{ $attributes->class([
            'block w-full rounded-lg border bg-white px-3 py-2 text-xs text-gray-900 shadow-sm',
            'border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-0' => !$errors->has($name),
            'border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-2 focus:ring-red-500' => $errors->has($name),
        ]) }}
    />

    @error($name)
        <p class="text-[11px] text-red-600">{{ $message }}</p>
    @enderror
</div>
