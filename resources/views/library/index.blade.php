<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            {{ __('Library') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <x-ad-banner />

            <section>
                <h2 class="text-lg font-bold mb-4 flex items-center">
                    <i class="fa-solid fa-clock-rotate-left text-gray-500 mr-2"></i>Reading History
                </h2>
                @if ($readingHistories->isEmpty())
                    <div class="p-6 text-center text-gray-600">
                        There is no reading history yet.
                    </div>
                @else
                    <ul class="bg-white border border-gray-300 rounded-md divide-y divide-gray-300 overflow-hidden">
                        @foreach ($readingHistories as $history)
                            @php($novel = $history->novel)
                            @php($chapter = $history->lastChapter)
                            <li>
                                <a href="{{ route('chapters.show', [$novel->slug, $chapter->chapter_number]) }}"
                                    class="flex items-center justify-between gap-4 px-4 py-3 hover:bg-gray-50 hover:text-blue-600 transition-colors">
                                    <div class="min-w-0">
                                        <p class="font-semibold truncate">{{ $novel->title }}</p>
                                        <p class="text-sm text-gray-500 mt-0.5">
                                            Continue Chapter {{ $chapter->chapter_number }}: {{ $chapter->title }}
                                        </p>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-gray-400 shrink-0"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <x-ad-banner />

            <section>
                <h2 class="text-lg font-bold mb-4 flex items-center">
                    <i class="fa-solid fa-bookmark text-gray-500 mr-2"></i>Bookmarks
                </h2>

                @if ($bookmarks->isEmpty())
                    <div class="p-6 text-center text-gray-600">
                        There are no bookmarked novels yet.
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        @foreach ($bookmarks as $bookmark)
                            @php($novel = $bookmark->novel)
                            <a href="{{ route('novels.show', $novel->slug) }}" class="group block">
                                <div class="aspect-[3/4] rounded-md bg-gray-200 flex items-center justify-center relative">
                                    @if ($novel->cover_image)
                                        <img src="{{ asset('storage/' . $novel->cover_image) }}" alt="{{ $novel->title }}" class="w-full h-full object-cover">
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
                        @endforeach
                    </div>
                    <div class="mt-6">
                        {{ $bookmarks->links() }}
                    </div>
                @endif
            </section>

            <x-ad-banner />

        </div>
    </div>
</x-app-layout>
