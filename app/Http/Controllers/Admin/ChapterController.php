<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChapterRequest;
use App\Http\Requests\UpdateChapterRequest;
use App\Models\Chapter;
use App\Models\Novel;

class ChapterController extends Controller
{
    /**
     * Show the form to add a new chapter.
     */
    public function create(Novel $novel)
    {
        return view('admin.chapters.create', compact('novel'));
    }

    /**
     * Store a new chapter under the given novel.
     */
    public function store(StoreChapterRequest $request, Novel $novel)
    {
        $novel->chapters()->create($request->validated());

        return redirect()->route('admin.novels.show', $novel)->with('status', 'Chapter added.');
    }

    /**
     * Show the form to edit a chapter.
     */
    public function edit(Novel $novel, Chapter $chapter)
    {
        return view('admin.chapters.edit', compact('novel', 'chapter'));
    }

    /**
     * Update an existing chapter.
     */
    public function update(UpdateChapterRequest $request, Novel $novel, Chapter $chapter)
    {
        $chapter->update($request->validated());

        return redirect()->route('admin.novels.show', $novel)->with('status', 'Chapter updated.');
    }

    /**
     * Delete a chapter.
     */
    public function destroy(Novel $novel, Chapter $chapter)
    {
        $chapter->delete();

        return redirect()->route('admin.novels.show', $novel)->with('status', 'Chapter deleted.');
    }
}
