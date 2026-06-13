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
    private const SORT_LABELS = [
        'new_release'      => 'New Release',
        'most_popular'     => 'Most Popular',
        'top_rated'        => 'Top Rated',
        'recently_updated' => 'Recently Updated',
    ];

    private const STATUS_LABELS = [
        'all'       => 'All Status',
        'published' => 'Published',
        'takedown'  => 'Takedown',
        'reported'  => 'Reported',
    ];

    /**
     * Display the admin dashboard.
     */
    public function index(Request $request)
    {
        $sort   = (string) $request->query('sort', 'new_release');
        $status = (string) $request->query('status', 'all');

        $novels = Novel::query()
            ->with('author')
            ->withCount(['chapters', 'reports'])
            ->when($request->filled('search'), fn($q) => $q->where(
                fn($q) => $q->where('title', 'like', "%{$request->search}%")
                             ->orWhere('synopsis', 'like', "%{$request->search}%")
            ))
            ->filterByStatus($status)
            ->sortBy($sort)
            ->paginate(20)
            ->withQueryString();

        $pendingApplications = AuthorApplication::with('user')
            ->where('status', 'pending')
            ->paginate(20);

        return view('admin.dashboard', [
            'totals'              => $this->getTotals(),
            'novels'              => $novels,
            'pendingApplications' => $pendingApplications,
            'sort'                => $sort,
            'status'              => $status,
            'sortLabels'          => self::SORT_LABELS,
            'statusLabels'        => self::STATUS_LABELS,
            'activeSortLabel'     => self::SORT_LABELS[$sort] ?? 'New Release',
            'activeStatusLabel'   => self::STATUS_LABELS[$status] ?? 'All Status',
        ]);
    }

    /**
     * Approve a pending author application.
     *
     * Wrapped in a transaction to keep application status and user role in sync.
     */
    public function approveAuthor(AuthorApplication $application)
    {
        DB::transaction(function () use ($application) {
            $application->update(['status' => 'approved']);
            $application->user->update(['role' => 'author']);
        });

        return back()->with('status', 'Author application approved.');
    }

    /**
     * Reject a pending author application.
     */
    public function rejectAuthor(AuthorApplication $application)
    {
        $application->update(['status' => 'rejected']);

        return back()->with('status', 'Author application rejected.');
    }

    /**
     * Toggle a novel's status between 'published' and 'takedown'.
     */
    public function toggleNovelStatus(Novel $novel)
    {
        $newStatus = $novel->status === 'takedown' ? 'published' : 'takedown';
        $novel->update(['status' => $newStatus]);

        $message = $newStatus === 'takedown' ? 'Novel takedown applied.' : 'Novel restored.';

        return back()->with('status', $message);
    }

    /**
     * Aggregate site-wide statistics for the dashboard header.
     */
    private function getTotals(): array
    {
        return [
            'users'    => User::count(),
            'authors'  => User::where('role', 'author')->count(),
            'novels'   => Novel::count(),
            'chapters' => Chapter::count(),
            'reads'    => Novel::sum('view_count'),
        ];
    }
}
