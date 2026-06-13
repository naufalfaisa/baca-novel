<?php

namespace App\Http\Controllers;

use App\Models\Novel;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    private const SORT_LABELS = [
        'new_release'      => 'New Release',
        'most_popular'     => 'Most Popular',
        'top_rated'        => 'Top Rated',
        'recently_updated' => 'Recently Updated',
    ];

    public function index(Request $request)
    {
        $search = (string) $request->query('search', '');
        $sort   = (string) $request->query('sort', 'new_release');

        $activeLabel = self::SORT_LABELS[$sort] ?? 'New Release';
        $statusText  = $search !== ''
            ? 'Search results for "' . $search . '" (' . $activeLabel . ')'
            : $activeLabel;

        $novels = Novel::query()
            ->where('status', 'published')
            ->when($search !== '', fn($q) => $q->where(
                fn($q) => $q->where('title', 'like', "%{$search}%")
                             ->orWhere('synopsis', 'like', "%{$search}%")
            ))
            ->sortBy($sort)
            ->paginate(20)
            ->withQueryString();

        return view('home', [
            'search'      => $search,
            'sort'        => $sort,
            'activeLabel' => $activeLabel,
            'statusText'  => $statusText,
            'novels'      => $novels,
            'sortLabels'  => self::SORT_LABELS,
        ]);
    }
}
