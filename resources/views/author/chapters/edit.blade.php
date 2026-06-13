<x-app-layout>
    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-back-button :href="route('author.novels.show', $novel)" class="!mb-0" />

            <div>
                <h1 class="text-2xl font-semibold mb-2">Edit Chapter</h1>
                <p class="text-gray-600 mb-6">{{ $novel->title }}</p>
            </div>

            <form action="{{ route('author.chapters.update', [$novel, $chapter]) }}" method="post" class="bg-white border border-gray-200 rounded-md p-6 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="chapter_number" class="block text-sm font-medium text-gray-700 mb-1">Chapter Number</label>
                    <input type="number" name="chapter_number" id="chapter_number" value="{{ old('chapter_number', $chapter->chapter_number) }}" required min="1" class="w-full border border-gray-300 bg-white rounded-md px-4 py-2">
                    @error('chapter_number')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Chapter Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $chapter->title) }}" required class="w-full border border-gray-300 bg-white rounded-md px-4 py-2">
                    @error('title')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                    <textarea name="content" id="content" rows="12" required class="w-full border border-gray-300 bg-white rounded-md px-4 py-2">{{ old('content', $chapter->content) }}</textarea>
                    @error('content')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-md font-medium transition-colors">Update Chapter</button>
                    <a href="{{ route('author.novels.show', $novel) }}" class="bg-gray-200 hover:bg-gray-300 px-3 py-1.5 rounded-md font-medium transition-colors">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
