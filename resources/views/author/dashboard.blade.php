<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            {{ __('Author Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <x-back-button :href="route('home')" class="!mb-0" />

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold">My Novels</h1>
                    <p class="text-gray-600 mt-1">Total views: {{ number_format($totalViews) }}</p>
                </div>
                <a href="{{ route('author.novels.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-md font-medium inline-flex items-center transition-colors">
                    <i class="fa-solid fa-plus mr-2"></i>Create Novel
                </a>
            </div>

            @if ($novels->isEmpty())
                <div class="p-6 text-center text-gray-600">
                    There are no novels yet.
                </div>
            @else
                <div class="bg-white border border-gray-300 rounded-md overflow-hidden">
                    <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-300 overflow-hidden">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Title</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Author</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Chapters</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-600">Views</th>
                                <th class="px-4 py-3 text-right text-sm font-medium text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-300">
                            @foreach ($novels as $novel)
                                <tr class="group hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3">
                                        <a
                                            href="{{ route('novels.show', $novel->slug) }}"
                                            class="font-semibold group-hover:text-blue-600 transition-colors"
                                        >
                                            {{ $novel->title }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $novel->author_name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $novel->chapters_count }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ number_format($novel->view_count) }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('author.novels.show', $novel) }}" class="text-gray-600 font-semibold text-sm hover:underline transition-colors">Manage</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>

                <div class="mt-4">
                    {{ $novels->links() }}
                </div>

            @endif
        </div>
    </div>
</x-app-layout>
