<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuthorApplication;
use App\Models\Chapter;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $totals = [
            'users' => User::count(),
            'authors' => User::where('role', 'author')->count(),
            'novels' => Novel::count(),
            'chapters' => Chapter::count(),
            'reads' => Novel::sum('view_count'),
        ];

        $sortLabels = [
            'new_release' => 'New Release',
            'most_popular' => 'Most Popular',
            'top_rated' => 'Top Rated',
            'recently_updated' => 'Recently Update'
        ];

        $statusLabels = [
            'all' => 'All Status',
            'published' => 'Published',
            'takedown' => 'Takedown',
            'reported' => 'Reported'
        ];

        $sort = (string) $request->query('sort', 'new_release');
        $status = (string) $request->query('status', 'all');

        $activeSortLabel = $sortLabels[$sort] ?? 'New Release';
        $activeStatusLabel = $statusLabels[$status] ?? 'All Status';

        $novelsQuery = Novel::query()
            ->with('author')
            ->withCount(['chapters', 'reports']);

        $novelsQuery->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($innerQuery) use ($search) {
                $innerQuery->where('title', 'like', "%{$search}%")
                    ->orWhere('synopsis', 'like', "%{$search}%");
            });
        });

        $novelsQuery->when($status !== 'all', function ($q) use ($status) {
            if ($status === 'reported') {
                $q->whereHas('reports');
            } else {
                $q->where('status', $status);
            }
        });

        match ($sort) {
            'most_popular' => $novelsQuery->orderBy('view_count', 'desc'),
            'top_rated' => $novelsQuery->withCount([
                'votes as upvotes' => fn($q) => $q->where('type', 'upvote'),
                'votes as downvotes' => fn($q) => $q->where('type', 'downvote'),
            ])->orderByRaw('(upvotes - downvotes) DESC'),
            'recently_updated' => $novelsQuery->orderBy(
                Chapter::select('updated_at')
                    ->whereColumn('novel_id', 'novels.id')
                    ->latest()
                    ->limit(1),
                'desc'
            )->orderBy('updated_at', 'desc'),
            default => $novelsQuery->orderBy('created_at', 'desc'),
        };

        $novels = $novelsQuery->paginate(20)->withQueryString();

        $pendingApplications = AuthorApplication::with('user')
            ->where('status', 'pending')
            ->paginate(20);

        return view('admin.dashboard', compact(
            'totals',
            'pendingApplications',
            'novels',
            'sort',
            'status',
            'sortLabels',
            'statusLabels',
            'activeSortLabel',
            'activeStatusLabel'
        ));
    }

    public function approveAuthor(AuthorApplication $application)
    {
        DB::transaction(function () use ($application) {
            $application->update(['status' => 'approved']);
            $application->user->update(['role' => 'author']);
        });

        return back()->with('status', 'Author application approved.');
    }

    public function rejectAuthor(AuthorApplication $application)
    {
        $application->update(['status' => 'rejected']);
        return back()->with('status', 'Author application rejected.');
    }

    public function toggleNovelStatus(Novel $novel)
    {
        $newStatus = $novel->status === 'takedown' ? 'published' : 'takedown';
        $novel->update(['status' => $newStatus]);

        $message = $newStatus === 'takedown' ? 'Novel takedown applied.' : 'Novel restored.';
        return back()->with('status', $message);
    }
}
