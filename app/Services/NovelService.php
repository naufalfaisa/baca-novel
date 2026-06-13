<?php

namespace App\Services;

use App\Models\Novel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NovelService
{
    /**
     * Generate a unique slug for a novel title.
     * Optionally exclude a specific novel ID (for updates).
     */
    public function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $baseSlug = Str::slug($title);

        $query = Novel::where('slug', 'LIKE', "{$baseSlug}%");

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $count = $query->count();

        return $count > 0 ? "{$baseSlug}-" . ($count + 1) : $baseSlug;
    }

    /**
     * Handle cover image upload or removal.
     * Returns an array of attributes to merge into the validated data.
     */
    public function handleCoverImage(Request $request, ?Novel $novel = null): array
    {
        if ($request->hasFile('cover_image')) {
            if ($novel?->cover_image) {
                Storage::disk('public')->delete($novel->cover_image);
            }

            return ['cover_image' => $request->file('cover_image')->store('covers', 'public')];
        }

        if ($request->boolean('remove_cover') && $novel?->cover_image) {
            Storage::disk('public')->delete($novel->cover_image);
            return ['cover_image' => null];
        }

        return [];
    }

    /**
     * Delete the cover image file for a novel (used on destroy).
     */
    public function deleteCoverImage(Novel $novel): void
    {
        if ($novel->cover_image) {
            Storage::disk('public')->delete($novel->cover_image);
        }
    }
}
