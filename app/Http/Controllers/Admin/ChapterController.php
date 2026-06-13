<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChapterRequest;
use App\Http\Requests\UpdateChapterRequest;
use App\Models\Chapter;
use App\Models\Novel;

class ChapterController extends Controller
{
    public function create(Novel $novel)
    {
        return view('admin.chapters.create', compact('novel'));
    }

    public function store(StoreChapterRequest $request, Novel $novel)
    {
        $novel->chapters()->create($request->validated());

        return redirect()->route('admin.novels.show', $novel)->with('status', 'Chapter berhasil ditambahkan.');
    }

    public function edit(Novel $novel, Chapter $chapter)
    {
        return view('admin.chapters.edit', compact('novel', 'chapter'));
    }

    public function update(UpdateChapterRequest $request, Novel $novel, Chapter $chapter)
    {
        $chapter->update($request->validated());

        return redirect()->route('admin.novels.show', $novel)->with('status', 'Chapter berhasil diperbarui.');
    }

    public function destroy(Novel $novel, Chapter $chapter)
    {
        $chapter->delete();

        return redirect()->route('admin.novels.show', $novel)->with('status', 'Chapter berhasil dihapus.');
    }
}
