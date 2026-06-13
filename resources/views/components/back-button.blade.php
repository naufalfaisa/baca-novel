@props([
    'href',
    'label' => 'Back',
])

<div {{ $attributes->merge(['class' => 'mb-6']) }}>
    <a
        href="{{ $href }}"
        class="inline-flex items-center text-gray-800 font-semibold transition-colors"
    >
        <i class="fa-solid fa-arrow-left mr-2"></i>{{ $label }}
    </a>
</div>
