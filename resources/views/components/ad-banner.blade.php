@auth
    @if (!Auth::user()->isPremium())
        <div {{ $attributes->merge(['class' => 'w-full']) }}>
            <div class="flex items-center justify-center bg-gray-200 border border-dashed border-gray-400 text-gray-500 text-sm font-semibold tracking-widest uppercase h-32 select-none">
                AD
            </div>
        </div>
    @endif
@else
    <div {{ $attributes->merge(['class' => 'w-full']) }}>
        <div class="flex items-center justify-center bg-gray-200 border border-dashed border-gray-400 text-gray-500 text-sm font-semibold tracking-widest uppercase h-32 select-none">
            AD
        </div>
    </div>
@endauth
