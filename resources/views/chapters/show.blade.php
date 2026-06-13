<x-app-layout>
    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-back-button :href="route('novels.show', $novel->slug)" class="!mb-0" />

            <h1 class="text-2xl font-bold mb-2">
                Chapter {{ $chapter->chapter_number }}: {{ $chapter->title }}
            </h1>

            <x-ad-banner />

            <div class="flex justify-between gap-4">
                @if ($previousChapter)
                    <a href="{{ route('chapters.show', [$novel->slug, $previousChapter->chapter_number]) }}" class="bg-gray-200 hover:bg-gray-300 px-3 py-1.5 rounded-md font-medium transition-colors">
                        <i class="fa-solid fa-chevron-left mr-1 text-xs"></i>Previous
                    </a>
                @else
                    <span></span>
                @endif
                @if ($nextChapter)
                    <a href="{{ route('chapters.show', [$novel->slug, $nextChapter->chapter_number]) }}" class="bg-gray-200 hover:bg-gray-300 px-3 py-1.5 rounded-md font-medium transition-colors">
                        Next<i class="fa-solid fa-chevron-right ml-1 text-xs"></i>
                    </a>
                @endif
            </div>

            <x-ad-banner />

            <article class="max-w-none leading-relaxed whitespace-pre-wrap text-base sm:text-lg">
                {{ $chapter->content }}
            </article>

            <x-ad-banner />

            <div class="flex justify-between gap-4">
                @if ($previousChapter)
                    <a href="{{ route('chapters.show', [$novel->slug, $previousChapter->chapter_number]) }}" class="bg-gray-200 hover:bg-gray-300 px-3 py-1.5 rounded-md font-medium transition-colors">
                        <i class="fa-solid fa-chevron-left mr-1 text-xs"></i>Previous
                    </a>
                @else
                    <span></span>
                @endif
                @if ($nextChapter)
                    <a href="{{ route('chapters.show', [$novel->slug, $nextChapter->chapter_number]) }}" class="bg-gray-200 hover:bg-gray-300 px-3 py-1.5 rounded-md font-medium transition-colors">
                        Next<i class="fa-solid fa-chevron-right ml-1 text-xs"></i>
                    </a>
                @endif
            </div>

            <x-ad-banner />

            <section class="py-6">
                <h2 class="text-lg font-bold mb-4">Comments</h2>

                @auth
                    <form action="{{ route('chapters.comment', $chapter) }}" method="post" class="mb-6">
                        @csrf
                        <textarea
                            name="content"
                            rows="3"
                            required
                            maxlength="1000"
                            placeholder="Write a comment"
                            class="w-full border-gray-300 bg-white rounded-md px-4 py-2"
                        >{{ old('content') }}</textarea>

                        @error('content')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <button type="submit" class="mt-2 bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-md font-medium transition-colors">
                            Send
                        </button>
                    </form>
                @else
                    <p class="text-gray-600 mb-4">
                        <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Login</a> to comment.
                    </p>
                @endauth
                <div class="space-y-3">
                    @forelse ($chapter->comments as $comment)
                        <div class="bg-white border border-gray-300 rounded-md p-4">
                            <p class="font-medium text-sm">{{ $comment->user->name }}</p>
                            <p class="mt-1 whitespace-pre-wrap">{{ $comment->content }}</p>
                        </div>
                    @empty
                        <p class="text-gray-600">No comments yet.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
