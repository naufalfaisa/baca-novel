<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChapterRequest;
use App\Http\Requests\UpdateChapterRequest;
use App\Models\Chapter;
use App\Models\Novel;

class ChapterController extends Controller
{
    public function create(Novel $novel)
    {
        $this->authorize('modify', $novel);

        return view('author.chapters.create', compact('novel'));
    }

    public function store(StoreChapterRequest $request, Novel $novel)
    {
        $this->authorize('modify', $novel);

        $novel->chapters()->create($request->validated());

        return redirect()->route('author.novels.show', $novel)->with('status', 'Chapter added.');
    }

    public function edit(Novel $novel, Chapter $chapter)
    {
        $this->authorize('modify', $novel);
        abort_if($chapter->novel_id !== $novel->id, 404);

        return view('author.chapters.edit', compact('novel', 'chapter'));
    }

    public function update(UpdateChapterRequest $request, Novel $novel, Chapter $chapter)
    {
        $this->authorize('modify', $novel);
        abort_if($chapter->novel_id !== $novel->id, 404);

        $chapter->update($request->validated());

        return redirect()->route('author.novels.show', $novel)->with('status', 'Chapter updated.');
    }

    public function destroy(Novel $novel, Chapter $chapter)
    {
        $this->authorize('modify', $novel);
        abort_if($chapter->novel_id !== $novel->id, 404);

        $chapter->delete();

        return redirect()->route('author.novels.show', $novel)->with('status', 'Chapter deleted.');
    }
}
