@php
    $showAd = Auth::guest() || !Auth::user()->isPremium();
    $adImage = '';
@endphp

@if ($showAd)
    <div {{ $attributes->merge(['class' => 'w-full']) }}>
        @if (!empty($adImage))
            <img
                src="{{ asset($adImage) }}"
                alt="Advertisement - Prabowo Subianto"
                class="w-full h-32 object-cover"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
            >
        @endif
        <div class="{{ !empty($adImage) ? 'hidden' : 'flex' }} items-center justify-center bg-gray-200 border border-dashed border-gray-400 text-gray-500 text-sm font-semibold tracking-widest uppercase h-32 select-none">
            AD
        </div>
    </div>
@endif
