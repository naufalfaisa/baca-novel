<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-ad-banner class="mb-6" />
            <x-back-button :href="route('home')" class="!mb-0" />

            <div class="flex flex-col md:flex-row gap-8 py-6">
                <div class="w-64 shrink-0">
                    <div class="aspect-[3/4] bg-gray-200 rounded-md flex items-center justify-center overflow-hidden">
                        @if ($novel->cover_image)
                            <img src="{{ asset('storage/' . $novel->cover_image) }}" alt="{{ $novel->title }}" class="w-full h-full object-cover">
                        @else
                            <i class="fa-solid fa-book text-5xl text-gray-400"></i>
                        @endif
                    </div>
                </div>

                <div class="flex-1">
                    <h1 class="text-2xl font-bold mb-2">{{ $novel->title }}</h1>
                    <div class="flex items-center gap-4 mb-4 text-gray-600 text-sm">
                        <span>
                            <i class="fa-solid fa-user mr-1"></i>{{ $novel->author_name }}
                        </span>
                        <span>
                            <i class="fa-solid fa-eye mr-1"></i>{{ number_format($novel->view_count) }} reads
                        </span>

                        <span class="inline-flex items-center">
                            <i class="fa-solid fa-arrow-up mr-1"></i>{{ $upvotes }}
                            <span class="mx-1">/</span>
                            <i class="fa-solid fa-arrow-down ml-1 mr-1"></i>{{ $downvotes }}
                        </span>
                    </div>

                    @if ($novel->status === 'takedown')
                        <div class="inline-flex items-center rounded-md bg-red-200 px-2 py-0.5 text-xs font-semibold text-red-600 mb-4">
                            <i class="fa-solid fa-triangle-exclamation mr-1"></i>The novel was taken down.
                        </div>
                    @endif
                    @if ($novel->genres->isNotEmpty())
                        <div class="flex flex-wrap gap-2 mb-4">
                            @foreach ($novel->genres as $genre)
                                <span class="inline-flex items-center rounded-md bg-gray-200 px-2 py-0.5 text-xs font-semibold">
                                    {{ $genre->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                    @if ($novel->synopsis)
                        <p class="text-gray-700 mb-6 leading-relaxed">{{ $novel->synopsis }}</p>
                    @endif

                    <div class="flex flex-wrap gap-3 mb-6">
                        @auth
                            @if ($novel->chapters->isNotEmpty())
                                <a href="{{ route('chapters.show', [$novel->slug, $novel->chapters->sortBy('chapter_number')->first()->chapter_number]) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-md font-medium transition-colors">
                                    <i class="fa-solid fa-book-open mr-1"></i>Read Now
                                </a>
                            @endif

                            <form action="{{ route('novels.bookmark', $novel) }}" method="post">
                                @csrf
                                <button type="submit" class="{{ $isBookmarked ? 'bg-gray-500 hover:bg-gray-600 text-white' : 'bg-gray-200 hover:bg-gray-300' }} px-3 py-1.5 rounded-md font-medium transition-colors">
                                    <i class="fa-{{ $isBookmarked ? 'solid' : 'regular' }} fa-bookmark mr-1"></i>
                                    {{ $isBookmarked ? 'Bookmarked' : 'Bookmark' }}
                                </button>
                            </form>

                            <form action="{{ route('novels.vote', $novel) }}" method="post">
                                @csrf
                                <input type="hidden" name="type" value="upvote">
                                <button type="submit" class="{{ ($userVote && $userVote->type === 'upvote') ? 'bg-green-500 text-white' : 'bg-gray-200 hover:bg-gray-300' }} px-3 py-1.5 rounded-md font-medium transition-colors">
                                    <i class="fa-solid fa-arrow-up"></i>
                                </button>
                            </form>

                            <form action="{{ route('novels.vote', $novel) }}" method="post">
                                @csrf
                                <input type="hidden" name="type" value="downvote">
                                <button type="submit" class="{{ ($userVote && $userVote->type === 'downvote') ? 'bg-red-500 text-white' : 'bg-gray-200 hover:bg-gray-300' }} px-3 py-1.5 rounded-md font-medium transition-colors">
                                    <i class="fa-solid fa-arrow-down"></i>
                                </button>
                            </form>

                            <form action="{{ route('novels.report', $novel) }}" method="post">
                                @csrf
                                <button type="submit" onclick="return confirm('{{ $isReported ? 'Cancel report of this novel?' : 'Report this novel to Admin?' }}')" class="{{ $isReported ? 'bg-gray-500 hover:bg-gray-600 text-white' : 'bg-gray-200 hover:bg-gray-300' }} px-3 py-1.5 rounded-md font-medium transition-colors">
                                    <i class="fa-{{ $isReported ? 'solid' : 'regular' }} fa-flag mr-1"></i>
                                    {{ $isReported ? 'Reported' : 'Report' }}
                                </button>
                            </form>
                        @endauth
                    </div>
                </div>
            </div>

            <x-ad-banner class="my-6" />

            <div class="mt-4">
                <h2 class="text-lg font-bold mb-4">Chapter List</h2>
                @if ($novel->chapters->isEmpty())
                    <p class="text-gray-600-400">There are no chapters available yet.</p>
                @else
                    <ul class="bg-white border border-gray-300 rounded-md divide-y divide-gray-300 overflow-hidden">
                        @foreach ($novel->chapters->sortBy('chapter_number') as $chapter)
                            <li>
                                <a href="{{ route('chapters.show', [$novel->slug, $chapter->chapter_number]) }}" class="flex items-center justify-between px-4 py-3 font-semibold hover:bg-gray-50 hover:text-blue-600 transition-colors">
                                    <span>Chapter {{ $chapter->chapter_number }}: {{ $chapter->title }}</span>
                                    <i class="fa-solid fa-chevron-right text-gray-400"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <x-ad-banner class="mt-8" />
        </div>
    </div>
</x-app-layout>
