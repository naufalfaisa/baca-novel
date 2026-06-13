<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class LibraryController extends Controller
{
    /**
     * Display the user's personal library.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('library.index', [
            'readingHistories' => $user->readingHistories()
                ->whereHas('lastChapter')
                ->with(['novel', 'lastChapter'])
                ->latest()
                ->take(20)
                ->get(),

            'bookmarks' => $user->bookmarks()
                ->with('novel')
                ->latest()
                ->paginate(20),
        ]);
    }
}
