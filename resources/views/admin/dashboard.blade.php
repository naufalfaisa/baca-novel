<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <div>
                <h2 class="text-lg font-semibold mb-4">Author Applications</h2>
                @if ($pendingApplications->isEmpty())
                    <div class="p-6 text-center">
                        There are no pending applications.
                    </div>
                @else
                    <div class="bg-white rounded-md divide-y divide-gray-200">
                        @foreach ($pendingApplications as $application)
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-4 py-4">
                                <div>
                                    <p class="font-medium">{{ $application->user->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $application->user->email }}</p>
                                </div>
                                <div class="flex gap-2">
                                    <form action="{{ route('admin.author-applications.approve', $application) }}" method="post">
                                        @csrf
                                        <button type="submit" class="bg-green-100 hover:bg-green-200 text-green-600 px-3 py-1.5 rounded-md font-semibold transition-colors text-sm">Approve</button>
                                    </form>
                                    <form action="{{ route('admin.author-applications.reject', $application) }}" method="post">
                                        @csrf
                                        <button type="submit" class="bg-red-100 hover:bg-red-200 text-red-600 px-3 py-1.5 rounded-md font-semibold transition-colors text-sm">Reject</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        {{ $pendingApplications->links() }}
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="bg-white rounded-md p-4">
                    <p class="text-sm text-gray-500">Total Users</p>
                    <p class="text-2xl font-bold">{{ number_format($totals['users']) }}</p>
                </div>
                <div class="bg-white rounded-md p-4">
                    <p class="text-sm text-gray-500">Total Authors</p>
                    <p class="text-2xl font-bold">{{ number_format($totals['authors']) }}</p>
                </div>
                <div class="bg-white rounded-md p-4">
                    <p class="text-sm text-gray-500">Total Novels</p>
                    <p class="text-2xl font-bold">{{ number_format($totals['novels']) }}</p>
                </div>
                <div class="bg-white rounded-md p-4">
                    <p class="text-sm text-gray-500">Total Chapters</p>
                    <p class="text-2xl font-bold">{{ number_format($totals['chapters']) }}</p>
                </div>
                <div class="bg-white rounded-md p-4">
                    <p class="text-sm text-gray-500">Total Reads</p>
                    <p class="text-2xl font-bold">{{ number_format($totals['reads']) }}</p>
                </div>
            </div>

            <div>
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
                    <h2 class="text-lg font-semibold">Novel List</h2>

                    <div class="flex flex-col sm:flex-row items-center gap-4 w-full md:w-auto">
                        <a href="{{ route('admin.novels.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md font-semibold text-sm transition-colors flex items-center justify-center w-full sm:w-auto shrink-0">
                            <i class="fa-solid fa-plus mr-2"></i> Add Novel
                        </a>

                        <form action="{{ route('admin.dashboard') }}" method="get" class="w-full sm:w-64 md:w-80">
                            <div class="flex w-full">
                                <div class="relative flex-1">
                                    <input
                                        type="text"
                                        name="search"
                                        value="{{ request('search') }}"
                                        placeholder="Search..."
                                        class="w-full border-none pl-4 pr-12 py-2 bg-white rounded-l-md rounded-r-none"
                                    >
                                    <div class="absolute inset-y-0 right-0 flex items-center">
                                        @if (request('search') && request('search') !== '')
                                            <a
                                                href="{{ route('admin.dashboard', request()->except('search')) }}"
                                                class="inline-flex h-9 w-9 items-center justify-center text-gray-500 pr-1"
                                                aria-label="Delete search"
                                            >
                                                <i class="fa-solid fa-xmark"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                @if(request('sort'))
                                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                                @endif
                                @if(request('status'))
                                    <input type="hidden" name="status" value="{{ request('status') }}">
                                @endif

                                <button
                                    type="submit"
                                    class="inline-flex shrink-0 items-center justify-center px-4 text-white bg-blue-500 hover:bg-blue-600 rounded-r-md transition"
                                    aria-label="Search"
                                >
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                            </div>
                        </form>

                        <div class="w-full sm:w-auto">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center justify-between w-full sm:w-auto px-4 py-2 font-semibold text-sm bg-white rounded-md hover:bg-gray-200 transition-colors">
                                        <span>{{ $activeSortLabel }}</span>
                                        <i class="fa-solid fa-chevron-down ml-2 text-xs"></i>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    @foreach($sortLabels as $key => $label)
                                        <x-dropdown-link href="{{ route('admin.dashboard', array_merge(request()->query(), ['sort' => $key])) }}">
                                            {{ $label }}
                                        </x-dropdown-link>
                                    @endforeach
                                </x-slot>
                            </x-dropdown>
                        </div>

                        <div class="w-full sm:w-auto">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center justify-between w-full sm:w-auto px-4 py-2 font-semibold text-sm bg-white rounded-md hover:bg-gray-200 transition-colors">
                                        <span>{{ $activeStatusLabel }}</span>
                                        <i class="fa-solid fa-chevron-down ml-2 text-xs"></i>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    @foreach($statusLabels as $key => $label)
                                        <x-dropdown-link href="{{ route('admin.dashboard', array_merge(request()->query(), ['status' => $key])) }}">
                                            {{ $label }}
                                        </x-dropdown-link>
                                    @endforeach
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>
                </div>

                @if ($novels->isEmpty())
                    <div class="bg-white rounded-md p-6 text-center text-gray-600">
                        There are no novels that match the filter yet.
                    </div>
                @else
                    <div class="bg-white rounded-md overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Novel</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Author</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Chapter</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Views</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Reports</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                                        <th class="px-4 py-3 text-right font-semibold text-gray-600">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($novels as $novel)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-4 py-3">
                                                <a href="{{ route('novels.show', $novel->slug) }}" class="font-semibold hover:underline transition-colors" target="_blank">
                                                    {{ $novel->title }}
                                                </a>
                                            </td>

                                            <td class="px-4 py-3 text-gray-700">{{ $novel->author_name }}</td>

                                            <td class="px-4 py-3 text-gray-700">{{ $novel->chapters_count }}</td>
                                            <td class="px-4 py-3 text-gray-700">{{ number_format($novel->view_count) }}</td>
                                            <td class="px-4 py-3 text-gray-700">{{ $novel->reports_count }}</td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-semibold {{ $novel->status === 'takedown' ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' }}">
                                                    {{ ucfirst($novel->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex justify-end gap-3 text-sm">
                                                    <a href="{{ route('admin.novels.show', $novel) }}" class="font-semibold text-gray-600 hover:underline" title="Manage Novel">
                                                        Manage
                                                    </a>

                                                    <form action="{{ route('admin.novels.toggle-status', $novel) }}" method="post" class="inline">
                                                        @csrf
                                                        <button type="submit" class="font-semibold {{ $novel->status === 'takedown' ? 'text-green-600' : 'text-red-600' }} hover:underline" title="{{ $novel->status === 'takedown' ? 'Restore' : 'Takedown' }}">
                                                            {{ $novel->status === 'takedown' ? 'Restore' : 'Takedown' }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mt-4">
                        {{ $novels->withQueryString()->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
