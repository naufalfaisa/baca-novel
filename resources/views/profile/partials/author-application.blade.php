<section class="space-y-6">
    @if ($user->role !== 'admin')
        <div>
            <h2 class="text-lg font-medium text-gray-900">Author</h2>
            <div class="mt-1 text-sm text-gray-600">
                @if ($user->role === 'author')
                    You are already registered as an author.
                    <a href="{{ route('author.dashboard') }}" class="text-green-600 hover:underline font-medium">Go to Author Dashboard</a>
                @elseif ($authorApplication && $authorApplication->status === 'pending')
                    <span class="text-amber-600 font-medium">Your author application is pending admin approval.</span>
                @elseif ($authorApplication && $authorApplication->status === 'rejected')
                    <span class="text-red-600 font-medium">Your author application was rejected. You can reapply.</span>
                    <form method="POST" action="{{ route('profile.applyAuthor') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium">Apply as Author</button>
                    </form>
                @else
                    Apply to become a novel author.
                    <form method="POST" action="{{ route('profile.applyAuthor') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium">Apply as Author</button>
                    </form>
                @endif
            </div>
        </div>
    @endif
</section>
