<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the author's dashboard.
     */
    public function index(Request $request)
    {
        $user       = $request->user();
        $novels     = $user->novels()->withCount('chapters')->paginate(20);
        $totalViews = $user->novels()->sum('view_count');

        return view('author.dashboard', compact('novels', 'totalViews'));
    }
}
