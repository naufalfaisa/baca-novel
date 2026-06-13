<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Novel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChapterController extends Controller
{
    public function create(Novel $novel)
    {
        return view('admin.chapters.create', compact('novel'));
    }

    public function store(Request $request, Novel $novel)
    {
        $validated = $request->validate([
            'chapter_number' => [
                'required', 'integer',
                Rule::unique('chapters')->where(fn($q) => $q->where('novel_id', $novel->id)),
            ],
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $novel->chapters()->create($validated);

        return redirect()->route('admin.dashboard')->with('status', 'Chapter berhasil ditambahkan.');
    }

    public function edit(Novel $novel, Chapter $chapter)
    {
        return view('admin.chapters.edit', compact('novel', 'chapter'));
    }

    public function update(Request $request, Novel $novel, Chapter $chapter)
    {
        $validated = $request->validate([
            'chapter_number' => [
                'required', 'integer',
                Rule::unique('chapters')
                    ->where(fn($q) => $q->where('novel_id', $novel->id))
                    ->ignore($chapter->id),
            ],
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $chapter->update($validated);

        return redirect()->route('admin.dashboard')->with('status', 'Chapter berhasil diperbarui.');
    }

    public function destroy(Novel $novel, Chapter $chapter)
    {
        $chapter->delete();
        return redirect()->route('admin.dashboard')->with('status', 'Chapter berhasil dihapus.');
    }
}
