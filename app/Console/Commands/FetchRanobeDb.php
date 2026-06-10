<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FetchRanobeDbData extends Command
{
    protected $signature = 'novel:fetch-api {--limit=50} {--page=1} {--force} {--skip-images} {--exclude-tags=}';
    protected $description = 'Fetch novel data from RanobeDB API';

    protected array $defaultExcludedTags = ['harem', 'ecchi', 'adult', 'erotic', 'sexual content'];

    public function handle(): int
    {
        $path = storage_path('app/data/ranobedb_series.json');

        if (File::exists($path) && !$this->option('force') && !$this->confirm('File sudah ada. Timpa?')) {
            return self::SUCCESS;
        }

        $list = Http::get('https://ranobedb.org/api/v0/series', [
            'limit' => $this->option('limit'),
            'page'  => $this->option('page'),
            'sort'  => 'Relevance desc'
        ])->json('series', []);

        $data = collect($list)
            ->filter(fn($item) => $item['id'] ?? null)
            ->map(function ($item) {
                $series = Http::get("https://ranobedb.org/api/v0/series/{$item['id']}")->json('series');
                usleep(250000);
                return $series;
            })
            ->filter()
            ->reject(function ($series) {
                if ($this->hasExcludedTag($series)) {
                    $title = $series['title'] ?? $series['romaji'] ?? 'Unknown';
                    $this->line("  [skip] {$title} (mengandung tag yang dikecualikan)");
                    return true;
                }
                return false;
            })
            ->transform(function ($series) {
                if (!$this->option('skip-images')) {
                    $this->downloadCover($series);
                }
                return $series;
            })
            ->values()
            ->all();

        if ($data) {
            File::put($path, json_encode([
                'fetched_at' => now()->toIso8601String(),
                'count'      => count($data),
                'series'     => $data
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        return self::SUCCESS;
    }

    private function downloadCover(array &$series): void
    {
        $img = collect($series['books'] ?? [])
            ->pluck('image')
            ->first(fn($img) => !empty($img['filename']) && empty($img['nsfw']) && empty($img['spoiler']));

        if (!$img) return;

        $localPath = "covers/{$img['filename']}";
        $series['_local_cover'] = $localPath;

        $disk = Storage::disk('public');
        if ($disk->exists($localPath) && !$this->option('force')) return;

        $response = Http::get("https://images.ranobedb.org/{$img['filename']}");
        if ($response->successful()) {
            $disk->put($localPath, $response->body());
        }
    }

    private function hasExcludedTag(array $series): bool
    {
        $excluded = $this->option('exclude-tags')
            ? Str::of($this->option('exclude-tags'))->lower()->explode(',')->map(fn($t) => trim($t))->all()
            : $this->defaultExcludedTags;

        $tags = collect($series['tags'] ?? [])
            ->pluck('name')
            ->map(fn($name) => Str::lower($name))
            ->all();

        return collect($excluded)->intersect($tags)->isNotEmpty();
    }
}
