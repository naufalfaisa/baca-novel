<x-app-layout>
    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-back-button :href="route('admin.dashboard')" class="!mb-0" />

            <h1 class="text-2xl font-semibold">Create Novel</h1>

            <form action="{{ route('admin.novels.store') }}" method="post" enctype="multipart/form-data" class="bg-white rounded-md p-6 space-y-4">
                @csrf

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-600 mb-1">Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required class="w-full bg-white border-gray-300 rounded-md px-4 py-2">
                    @error('title')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="author_name" class="block text-sm font-medium text-gray-600 mb-1">Author Name</label>
                    <input type="text" name="author_name" id="author_name" value="{{ old('author_name', Auth::user()->name) }}" required class="w-full bg-white border-gray-300 rounded-md px-4 py-2">
                    @error('author_name')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="synopsis" class="block text-sm font-medium text-gray-600 mb-1">Synopsis</label>
                    <textarea name="synopsis" id="synopsis" rows="5" class="w-full rounded-md px-4 py-2 bg-white border-gray-300">{{ old('synopsis') }}</textarea>
                    @error('synopsis')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div x-data="{
                    coverUrl: null,
                    previewCover(event) {
                        const file = event.target.files[0];
                        if (file) {
                            this.coverUrl = URL.createObjectURL(file);
                        }
                    },
                    removeCover() {
                        this.coverUrl = null;
                        this.$refs.coverInput.value = '';
                    }
                }" class="space-y-2">
                    <label for="cover_image" class="block text-sm font-medium text-gray-600 mb-1">Cover Art <span class="text-gray-400 font-normal">(Optional)</span></label>

                    <div x-show="coverUrl !== null" class="mb-2" style="display: none;">
                        <img :src="coverUrl" alt="Cover Preview" class="h-32 w-auto rounded-md border border-gray-200">
                        <button type="button" @click="removeCover()" class="mt-2 flex items-center gap-1 text-sm text-red-600 hover:text-red-700">
                            <i class="fa-solid fa-xmark"></i> Remove cover art
                        </button>
                    </div>

                    <input type="file" name="cover_image" id="cover_image" x-ref="coverInput" accept="image/*" @change="previewCover($event)"
                        class="w-full text-sm text-gray-600 file:mr-4 file:px-3 file:py-1.5 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 hover:file:bg-gray-200">
                    @error('cover_image')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-2">Genre</label>
                    @if ($genres->isEmpty())
                        <p class="text-sm text-gray-500">There are no genres available yet.</p>
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach ($genres as $genre)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="genres[]" value="{{ $genre->id }}" {{ in_array($genre->id, old('genres', [])) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                                    <span class="text-sm text-gray-700">{{ $genre->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('genres')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    @endif
                </div>

                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-md font-medium transition-colors">Save Novel</button>
            </form>
        </div>
    </div>
</x-app-layout>
