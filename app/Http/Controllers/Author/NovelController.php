<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNovelRequest;
use App\Http\Requests\UpdateNovelRequest;
use App\Models\Genre;
use App\Models\Novel;
use App\Services\NovelService;
use Illuminate\Support\Facades\Auth;

class NovelController extends Controller
{
    public function __construct(private readonly NovelService $novelService) {}

    /**
     * Show the form to create a new novel.
     */
    public function create()
    {
        $genres = Genre::orderBy('name')->get();

        return view('author.novels.create', compact('genres'));
    }

    /**
     * Store a newly created novel.
     */
    public function store(StoreNovelRequest $request)
    {
        $validated = $request->validated();
        $validated['slug']      = $this->novelService->generateUniqueSlug($validated['title']);
        $validated['author_id'] = Auth::id();
        $validated              = array_merge($validated, $this->novelService->handleCoverImage($request));

        $novel = Novel::create($validated);
        $novel->genres()->sync($request->input('genres', []));

        return redirect()->route('author.dashboard')->with('status', 'Novel created.');
    }

    /**
     * Display a novel with its paginated chapter list.
     */
    public function show(Novel $novel)
    {
        $this->authorize('modify', $novel);

        $chapters = $novel->chapters()->orderBy('chapter_number')->paginate(20);

        return view('author.novels.show', compact('novel', 'chapters'));
    }

    /**
     * Show the form to edit a novel.
     */
    public function edit(Novel $novel)
    {
        $this->authorize('modify', $novel);

        $genres         = Genre::orderBy('name')->get();
        $selectedGenres = $novel->genres->pluck('id')->toArray();

        return view('author.novels.edit', compact('novel', 'genres', 'selectedGenres'));
    }

    /**
     * Update an existing novel.
     *
     * Slug is only regenerated when the title has changed.
     */
    public function update(UpdateNovelRequest $request, Novel $novel)
    {
        $this->authorize('modify', $novel);

        $validated = $request->validated();

        if ($request->title !== $novel->title) {
            $validated['slug'] = $this->novelService->generateUniqueSlug($validated['title'], $novel->id);
        }

        $validated = array_merge($validated, $this->novelService->handleCoverImage($request, $novel));

        $novel->update($validated);
        $novel->genres()->sync($request->input('genres', []));

        return redirect()->route('author.novels.show', $novel)->with('status', 'Novel updated.');
    }

    /**
     * Delete a novel and its cover image from storage.
     */
    public function destroy(Novel $novel)
    {
        $this->authorize('modify', $novel);

        $this->novelService->deleteCoverImage($novel);
        $novel->delete();

        return redirect()->route('author.dashboard')->with('status', 'Novel deleted.');
    }
}
