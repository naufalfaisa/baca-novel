<?php

namespace App\Http\Controllers;

use App\Http\Requests\VoteRequest;
use App\Models\Bookmark;
use App\Models\Novel;
use App\Models\NovelReport;
use App\Models\Vote;
use Illuminate\Support\Facades\Auth;

class NovelController extends Controller
{
    /**
     * Display the novel detail page.
     */
    public function show(string $slug)
    {
        $novel = Novel::where('slug', $slug)->with('chapters', 'genres')->firstOrFail();

        // Takedown novels are hidden from non-admin users.
        if ($novel->status === 'takedown' && (! Auth::check() || Auth::user()->role !== 'admin')) {
            abort(404);
        }

        $upvotes   = $novel->votes()->where('type', 'upvote')->count();
        $downvotes = $novel->votes()->where('type', 'downvote')->count();

        $userVote     = null;
        $isBookmarked = false;
        $isReported   = false;

        // Resolve the authenticated user's current interaction state.
        if (Auth::check()) {
            $userId       = Auth::id();
            $userVote     = Vote::where('user_id', $userId)->where('novel_id', $novel->id)->first();
            $isBookmarked = Bookmark::where('user_id', $userId)->where('novel_id', $novel->id)->exists();
            $isReported   = NovelReport::where('user_id', $userId)->where('novel_id', $novel->id)->exists();
        }

        return view('novels.show', compact('novel', 'upvotes', 'downvotes', 'userVote', 'isBookmarked', 'isReported'));
    }

    /**
     * Toggle the bookmark state for a novel.
     */
    public function toggleBookmark(Novel $novel)
    {
        $userId   = Auth::id();
        $bookmark = Bookmark::where('user_id', $userId)->where('novel_id', $novel->id)->first();

        if ($bookmark) {
            $bookmark->delete();
            return back()->with('status', 'Novel removed from bookmarks.');
        }

        Bookmark::create(['user_id' => $userId, 'novel_id' => $novel->id]);

        return back()->with('status', 'Novel added to bookmarks.');
    }

    /**
     * Cast or toggle a vote on a novel.
     */
    public function vote(VoteRequest $request, Novel $novel)
    {
        $userId       = Auth::id();
        $existingVote = Vote::where('user_id', $userId)->where('novel_id', $novel->id)->first();

        // Submitting the same type again removes the vote (toggle behavior).
        if ($existingVote && $existingVote->type === $request->type) {
            $existingVote->delete();
            return back()->with('status', 'Vote removed.');
        }

        Vote::updateOrCreate(
            ['user_id' => $userId, 'novel_id' => $novel->id],
            ['type' => $request->type]
        );

        return back()->with('status', $existingVote ? 'Vote changed.' : 'Vote casted.');
    }

    /**
     * Toggle a report flag on a novel.
     */
    public function report(Novel $novel)
    {
        $userId = Auth::id();
        $report = NovelReport::where('user_id', $userId)->where('novel_id', $novel->id)->first();

        if ($report) {
            $report->delete();
            return back()->with('status', 'Novel report dibatalkan.');
        }

        NovelReport::create(['user_id' => $userId, 'novel_id' => $novel->id]);

        return back()->with('status', 'Novel reported.');
    }
}
