<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Genre;
use App\Models\Novel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $novels = $user->novels()->withCount('chapters')->paginate(20);
        $totalViews = $user->novels()->sum('view_count');

        return view('author.dashboard', compact('novels', 'totalViews'));
    }

    public function create()
    {
        $genres = Genre::orderBy('name')->get();
        return view('author.novels.create', compact('genres'));
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
        $validated['author_id'] = Auth::id();

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        $novel = Novel::create($validated);
        $novel->genres()->sync($request->input('genres', []));

        return redirect()->route('author.dashboard')->with('status', 'Novel created.');
    }

    public function show(Novel $novel)
    {
        $this->authorizeNovelOwner($novel);
        $chapters = $novel->chapters()->orderBy('chapter_number')->paginate(20);

        return view('author.novels.show', compact('novel', 'chapters'));
    }

    public function edit(Novel $novel)
    {
        $this->authorizeNovelOwner($novel);
        $genres = Genre::orderBy('name')->get();
        $selectedGenres = $novel->genres->pluck('id')->toArray();
        return view('author.novels.edit', compact('novel', 'genres', 'selectedGenres'));
    }

    public function update(Request $request, Novel $novel)
    {
        $this->authorizeNovelOwner($novel);

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

        return redirect()->route('author.novels.show', $novel)->with('status', 'Novel updated.');
    }

    public function destroy(Novel $novel)
    {
        $this->authorizeNovelOwner($novel);

        if ($novel->cover_image) {
            Storage::disk('public')->delete($novel->cover_image);
        }

        $novel->delete();

        return redirect()->route('author.dashboard')->with('status', 'Novel deleted.');
    }

    public function createChapter(Novel $novel)
    {
        $this->authorizeNovelOwner($novel);
        return view('author.chapters.create', compact('novel'));
    }

    public function storeChapter(Request $request, Novel $novel)
    {
        $this->authorizeNovelOwner($novel);

        $validated = $request->validate([
            'chapter_number' => [
                'required',
                'integer',
                Rule::unique('chapters')->where(fn($q) => $q->where('novel_id', $novel->id)),
            ],
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $novel->chapters()->create($validated);

        return redirect()->route('author.novels.show', $novel)->with('status', 'Chapter added.');
    }

    public function editChapter(Novel $novel, Chapter $chapter)
    {
        $this->authorizeChapterOwner($novel, $chapter);
        return view('author.chapters.edit', compact('novel', 'chapter'));
    }

    public function updateChapter(Request $request, Novel $novel, Chapter $chapter)
    {
        $this->authorizeChapterOwner($novel, $chapter);

        $validated = $request->validate([
            'chapter_number' => [
                'required',
                'integer',
                Rule::unique('chapters')
                    ->where(fn($q) => $q->where('novel_id', $novel->id))
                    ->ignore($chapter->id),
            ],
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $chapter->update($validated);

        return redirect()->route('author.novels.show', $novel)->with('status', 'Chapter updated.');
    }

    public function destroyChapter(Novel $novel, Chapter $chapter)
    {
        $this->authorizeChapterOwner($novel, $chapter);
        $chapter->delete();

        return redirect()->route('author.novels.show', $novel)->with('status', 'Chapter deleted.');
    }

    private function authorizeNovelOwner(Novel $novel): void
    {
        if ($novel->author_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }

    private function authorizeChapterOwner(Novel $novel, Chapter $chapter): void
    {
        if ($novel->author_id !== Auth::id() || $chapter->novel_id !== $novel->id) {
            abort(403, 'Unauthorized action.');
        }
    }

    private function handleCoverImage(Request $request, Novel $novel): array
    {
        if ($request->hasFile('cover_image')) {
            if ($novel->cover_image) {
                Storage::disk('public')->delete($novel->cover_image);
            }
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

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $count = $query->count();
        return $count > 0 ? "{$baseSlug}-" . ($count + 1) : $baseSlug;
    }
}
