<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-back-button :href="route('admin.dashboard')" class="!mb-0" />

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl font-semibold mb-2">{{ $novel->title }}</h1>
                    <div class="flex items-center gap-4 text-gray-600 text-sm">
                        <span>
                            <i class="fa-solid fa-user mr-1"></i>{{ $novel->author_name }}
                        </span>
                        <span>
                            <i class="fa-solid fa-eye mr-1"></i>{{ number_format($novel->view_count) }} views
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.chapters.create', $novel) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-md font-medium inline-flex items-center shrink-0 transition-colors">
                        <i class="fa-solid fa-plus mr-1"></i> Add Chapter
                    </a>
                    <a href="{{ route('admin.novels.edit', $novel) }}" class="bg-gray-200 hover:bg-gray-300 px-3 py-1.5 rounded-md font-medium inline-flex items-center shrink-0 transition-colors">
                        <i class="fa-solid fa-pen mr-1"></i> Edit Novel
                    </a>
                    <form method="POST" action="{{ route('admin.novels.destroy', $novel) }}" onsubmit="return confirm('Delete this novel and all its chapters?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-100 hover:bg-red-200 text-red-600 px-3 py-1.5 rounded-md font-medium inline-flex items-center shrink-0 transition-colors">
                            <i class="fa-solid fa-trash mr-1"></i> Delete Novel
                        </button>
                    </form>
                </div>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-4">Chapter List</h2>

                @if ($chapters->isEmpty())
                    <p class="text-gray-600">There are no chapters yet.</p>
                @else
                    <ul class="bg-white border border-gray-300 rounded-md divide-y divide-gray-300 overflow-hidden">
                        @foreach ($chapters as $chapter)
                            <li class="flex items-center justify-between px-4 py-3 group hover:bg-gray-50 transition-colors">
                                <a
                                    href="{{ route('chapters.show', [$novel->slug, $chapter->chapter_number]) }}"
                                    class="font-semibold truncate max-w-[70%] group-hover:text-blue-600 transition-colors"
                                    title="Chapter {{ $chapter->chapter_number }}: {{ $chapter->title }}"
                                >
                                    Chapter {{ $chapter->chapter_number }}: {{ $chapter->title }}
                                </a>

                                <div class="flex items-center gap-4 shrink-0">
                                    <a href="{{ route('admin.chapters.edit', [$novel, $chapter]) }}" class="text-gray-600 font-semibold text-sm hover:underline transition-colors">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.chapters.destroy', [$novel, $chapter]) }}" onsubmit="return confirm('Delete this chapter?')" class="flex items-center">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 font-semibold text-sm hover:underline transition-colors">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-4">
                        {{ $chapters->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
