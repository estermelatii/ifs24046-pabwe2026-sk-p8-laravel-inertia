<?php

namespace App\Features\Post\Policies;

use App\Features\Post\Models\Post;
use App\Models\User;

class PostPolicy
{
    /** Pemilik atau admin (read-only) boleh melihat. */
    public function view(User $user, Post $post): bool
    {
        return $user->isAdmin() || $post->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isMember();
    }

    /** Hanya pemilik (member) yang boleh mengubah. Admin read-only. */
    public function update(User $user, Post $post): bool
    {
        return $user->isMember() && $post->user_id === $user->id;
    }

    public function delete(User $user, Post $post): bool
    {
        return $this->update($user, $post);
    }
}
