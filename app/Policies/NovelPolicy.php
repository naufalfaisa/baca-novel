<?php

namespace App\Policies;

use App\Models\Novel;
use App\Models\User;

class NovelPolicy
{
    /**
     * Only the novel owner (author) can modify their novel.
     */
    public function modify(User $user, Novel $novel): bool
    {
        return $user->id === $novel->author_id;
    }
}
