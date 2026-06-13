@props([
    'type' => 'info', // success, error, warning, info
    'messages' => null,
])

@php
    $styles = [
        'success' => 'bg-green-50 border-green-300 text-green-700',
        'error'   => 'bg-red-50 border-red-300 text-red-700',
        'warning' => 'bg-yellow-50 border-yellow-300 text-yellow-700',
        'info'    => 'bg-blue-50 border-blue-300 text-blue-700',
    ];
    $class = $styles[$type] ?? $styles['info'];
@endphp

@if ($messages || $slot->isNotEmpty())
    <div {{ $attributes->merge(['class' => "border rounded-md p-4 text-sm $class"]) }}>
        @if ($messages)
            <ul class="list-disc list-inside space-y-1">
                @foreach ((array) $messages as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        @else
            {{ $slot }}
        @endif
    </div>
@endif
