<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Chapter;
use App\Models\Novel;
use App\Models\ReadingHistory;
use Illuminate\Support\Facades\Auth;

class ChapterController extends Controller
{
    /**
     * Display a chapter for reading.
     */
    public function show(string $slug, int $chapterNumber)
    {
        $novel = Novel::where('slug', $slug)->firstOrFail();

        // Takedown novels are hidden from non-admin users.
        if ($novel->status === 'takedown' && (! Auth::check() || Auth::user()->role !== 'admin')) {
            abort(404);
        }

        $chapter = Chapter::where('novel_id', $novel->id)
            ->where('chapter_number', $chapterNumber)
            ->with(['comments.user'])
            ->firstOrFail();

        $novel->increment('view_count');

        $previousChapter = Chapter::where('novel_id', $novel->id)
            ->where('chapter_number', '<', $chapterNumber)
            ->orderBy('chapter_number', 'desc')
            ->first();

        $nextChapter = Chapter::where('novel_id', $novel->id)
            ->where('chapter_number', '>', $chapterNumber)
            ->orderBy('chapter_number', 'asc')
            ->first();

        // Track the last read chapter for authenticated users.
        if (Auth::check()) {
            ReadingHistory::updateOrCreate(
                ['user_id' => Auth::id(), 'novel_id' => $novel->id],
                ['last_chapter_id' => $chapter->id]
            );

            // Keep only the 20 most recent reading histories per user.
            $keepIds = ReadingHistory::where('user_id', Auth::id())
                ->latest()
                ->take(20)
                ->pluck('id');

            ReadingHistory::where('user_id', Auth::id())
                ->whereNotIn('id', $keepIds)
                ->delete();
        }

        return view('chapters.show', compact('novel', 'chapter', 'previousChapter', 'nextChapter'));
    }

    /**
     * Store a new comment on a chapter.
     */
    public function storeComment(StoreCommentRequest $request, Chapter $chapter)
    {
        $request->user()->comments()->create([
            'chapter_id' => $chapter->id,
            'content'    => $request->validated('content'),
        ]);

        return back()->with('status', 'Comment posted.');
    }
}
