<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <h2 class="font-semibold text-xl leading-tight">
                {{ $statusText }}
            </h2>

            <div class="flex flex-col sm:flex-row items-center gap-4 w-full md:w-auto">
                <form action="{{ route('home') }}" method="get" class="w-full sm:w-64 md:w-80">
                    <div class="flex w-full">
                        <div class="relative flex-1">
                            <input
                                type="text"
                                name="search"
                                value="{{ $search }}"
                                placeholder="Search..."
                                class="w-full border-none pl-4 pr-12 py-2 bg-gray-100 rounded-l-md rounded-r-none"
                            >
                            <div class="absolute inset-y-0 right-0 flex items-center">
                                @if ($search !== '')
                                    <a
                                        href="{{ route('home', ['sort' => $sort]) }}"
                                        class="inline-flex h-9 w-9 items-center justify-center text-gray-500 pr-1"
                                        aria-label="Delete search"
                                    >
                                        <i class="fa-solid fa-xmark"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                        <button
                            type="submit"
                            class="inline-flex shrink-0 items-center justify-center px-4 text-white bg-blue-500 hover:bg-blue-600 rounded-r-md transition"
                            aria-label="Search novel"
                            >
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </form>

                <div class="w-full sm:w-auto">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center justify-between w-full sm:w-auto px-3 py-1.5 font-semibold text-sm bg-gray-100 rounded-md hover:bg-gray-200 transition-colors">
                                <span>Sort by</span>
                                <i class="fa-solid fa-chevron-down ml-2 text-xs"></i>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            @foreach($sortLabels as $key => $label)
                                <x-dropdown-link href="{{ route('home', array_merge(request()->query(), ['sort' => $key])) }}">
                                    {{ $label }}
                                </x-dropdown-link>
                            @endforeach
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-ad-banner class="mb-6" />
            @if ($novels->isEmpty())
                <div class="p-6 text-center text-gray-500">
                    There are no novels available yet.
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @foreach ($novels as $index => $novel)
                        <a href="{{ route('novels.show', $novel->slug) }}" class="group block">
                            <div class="aspect-[3/4] rounded-md bg-gray-200 flex items-center justify-center overflow-hidden">
                                @if ($novel->cover_image)
                                    <img
                                        src="{{ asset('storage/' . $novel->cover_image) }}"
                                        alt="{{ $novel->title }}"
                                        class="w-full h-full object-cover">
                                @else
                                    <i class="fa-solid fa-book text-4xl text-gray-400"></i>
                                @endif
                            </div>
                            <div class="p-2">
                                <span class="font-semibold line-clamp-2 group-hover:text-blue-600 transition-colors">
                                    {{ $novel->title }}
                                </span>
                            </div>
                        </a>
                        @if ($index === 9)
                            </div>
                            <x-ad-banner class="my-4" />
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        @endif
                    @endforeach
                </div>
                <div class="mt-8">
                    {{ $novels->links() }}
                </div>
                <x-ad-banner class="mt-6" />
            @endif
        </div>
    </div>
</x-app-layout>
