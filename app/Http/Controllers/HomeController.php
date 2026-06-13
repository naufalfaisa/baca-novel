<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Novel;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $search = (string) $request->query('search', '');
        $sort = (string) $request->query('sort', 'new_release');

        $sortLabels = [
            'new_release' => 'New Release',
            'most_popular' => 'Most Popular',
            'top_rated' => 'Top Rated',
            'recently_updated' => 'Recently Updated',
        ];

        $activeLabel = $sortLabels[$sort] ?? 'New Release';

        if ($search !== '') {
            $statusText = 'Search results for "' . $search . '" (' . $activeLabel . ')';
        } else {
            $statusText = $activeLabel;
        }

        $query = Novel::query()->where('status', 'published');

        $query->when($search !== '', function ($q) use ($search) {
            $q->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('synopsis', 'like', '%' . $search . '%');
            });
        });

        match ($sort) {
            'most_popular' => $query->orderBy('view_count', 'desc'),
            'top_rated' => $query->withCount([
                'votes as upvotes' => fn($q) => $q->where('type', 'upvote'),
                'votes as downvotes' => fn($q) => $q->where('type', 'downvote'),
            ])->orderByRaw('(upvotes - downvotes) DESC'),
            'recently_updated' => $query->orderBy(
                Chapter::select('updated_at')
                    ->whereColumn('novel_id', 'novels.id')
                    ->latest()
                    ->limit(1),
                'desc'
            )->orderBy('updated_at', 'desc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        return view('home', [
            'search' => $search,
            'sort' => $sort,
            'activeLabel' => $activeLabel,
            'statusText' => $statusText,
            'novels' => $query->paginate(20)->withQueryString(),
            'sortLabels' => $sortLabels,
        ]);
    }
}
