<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\Novel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NovelController extends Controller
{
    private const DEFAULT_ADMIN_ID = 1;

    public function create()
    {
        $genres = Genre::orderBy('name')->get();
        return view('admin.novels.create', compact('genres'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author_name' => 'required|string|max:255',
            'synopsis' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2560',
            'genres' => 'nullable|array',
            'genres.*' => 'exists:genres,id',
        ]);

        $validated['slug'] = $this->generateUniqueSlug($validated['title']);

        $validated['author_id'] = self::DEFAULT_ADMIN_ID;

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        $novel = Novel::create($validated);
        $novel->genres()->sync($request->input('genres', []));

        return redirect()->route('admin.dashboard')->with('status', 'Novel berhasil dibuat oleh Admin.');
    }

    public function show(Novel $novel)
    {
        $chapters = $novel->chapters()->orderBy('chapter_number')->paginate(20);
        return view('admin.novels.show', compact('novel', 'chapters'));
    }

    public function edit(Novel $novel)
    {
        $genres = Genre::orderBy('name')->get();
        $selectedGenres = $novel->genres->pluck('id')->toArray();
        return view('admin.novels.edit', compact('novel', 'genres', 'selectedGenres'));
    }

    public function update(Request $request, Novel $novel)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author_name' => 'required|string|max:255',
            'synopsis' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2560',
            'genres' => 'nullable|array',
            'genres.*' => 'exists:genres,id',
        ]);

        if ($request->title !== $novel->title) {
            $validated['slug'] = $this->generateUniqueSlug($validated['title'], $novel->id);
        }

        $validated = array_merge($validated, $this->handleCoverImage($request, $novel));

        $novel->update($validated);
        $novel->genres()->sync($request->input('genres', []));

        return redirect()->route('admin.dashboard')->with('status', 'Novel berhasil diperbarui.');
    }

    public function destroy(Novel $novel)
    {
        if ($novel->cover_image) {
            Storage::disk('public')->delete($novel->cover_image);
        }
        $novel->delete();

        return redirect()->route('admin.dashboard')->with('status', 'Novel berhasil dihapus.');
    }

    private function handleCoverImage(Request $request, Novel $novel): array
    {
        if ($request->hasFile('cover_image')) {
            if ($novel->cover_image) Storage::disk('public')->delete($novel->cover_image);
            return ['cover_image' => $request->file('cover_image')->store('covers', 'public')];
        }
        if ($request->boolean('remove_cover') && $novel->cover_image) {
            Storage::disk('public')->delete($novel->cover_image);
            return ['cover_image' => null];
        }
        return [];
    }

    private function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $baseSlug = Str::slug($title);
        $query = Novel::where('slug', 'LIKE', "{$baseSlug}%");
        if ($excludeId) $query->where('id', '!=', $excludeId);

        $count = $query->count();
        return $count > 0 ? "{$baseSlug}-" . ($count + 1) : $baseSlug;
    }
}
