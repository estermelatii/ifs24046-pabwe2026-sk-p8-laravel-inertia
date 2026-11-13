<?php

namespace App\Features\Post\Contracts;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PostRepositoryInterface
{
    public function paginateForUser(User $user, ?string $search = null, ?string $status = null, int $perPage = 10): LengthAwarePaginator;

    public function paginatePublished(?string $search = null, ?string $category = null, int $perPage = 9): LengthAwarePaginator;

    public function paginateAll(?string $search = null, int $perPage = 10): LengthAwarePaginator;

    public function allForUser(User $user): Collection;

    public function all(): Collection;

    public function countForUser(User $user, ?bool $published = null): int;

    public function findPublishedBySlug(string $slug): ?Post;

    public function slugExists(string $slug, ?int $exceptId = null): bool;

    public function create(array $data): Post;

    public function update(Post $post, array $data): Post;

    public function delete(Post $post): bool;
}
