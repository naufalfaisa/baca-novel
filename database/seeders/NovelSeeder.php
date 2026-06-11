<?php

namespace Database\Seeders;

use App\Models\Genre;
use App\Models\Novel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NovelSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/data/ranobedb_series.json');

        if (!file_exists($path)) {
            $this->command->warn('File ranobedb_series.json not found. Run: php artisan novel:fetch-api');
            return;
        }

        $series = json_decode(file_get_contents($path), true)['series'] ?? [];

        $admin = User::where('role', 'admin')->firstOrFail();

        foreach ($series as $item) {
            $staffAuthor = collect($item['staff'] ?? [])
                ->firstWhere('role_type', 'author');

            $penName = $staffAuthor['romaji'] ?? $staffAuthor['name'] ?? 'Unknown Author';

            $title = collect($item['titles'] ?? [])
                ->firstWhere('lang', 'en')['title']
                ?? $item['titles'][0]['title']
                ?? 'Untitled';

            $baseSlug = Str::slug($title) ?: 'novel-' . $item['id'];
            $slug     = $baseSlug;

            if (Novel::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $item['id'];
            }

            $novel = Novel::firstOrCreate(
                ['slug' => $slug],
                [
                    'author_id'   => $admin->id,
                    'author_name'    => $penName,
                    'title'       => $title,
                    'synopsis'    => $item['book_description']['description']
                        ?? null,
                    'cover_image' => $item['_local_cover'] ?? null,
                    'status'      => 'published',
                ]
            );

            $genreIds = collect($item['tags'] ?? [])
                ->where('ttype', 'genre')
                ->pluck('name')
                ->filter()
                ->map(fn($name) => Genre::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => $name]
                )->id)
                ->toArray();

            $novel->genres()->syncWithoutDetaching($genreIds);
        }
    }
}
