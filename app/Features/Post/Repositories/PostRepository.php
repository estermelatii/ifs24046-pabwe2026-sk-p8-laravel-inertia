<?php

namespace App\Features\Post\Repositories;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PostRepository implements PostRepositoryInterface
{
    public function paginateForUser(User $user, ?string $search = null, ?string $status = null, int $perPage = 10): LengthAwarePaginator
    {
        return Post::query()
            ->with('user')
            ->where('user_id', $user->id)
            ->tap(fn (Builder $query) => $this->applySearch($query, $search))
            ->when($status === 'published', fn (Builder $query) => $query->where('is_published', true))
            ->when($status === 'draft', fn (Builder $query) => $query->where('is_published', false))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginatePublished(?string $search = null, ?string $category = null, int $perPage = 9): LengthAwarePaginator
    {
        return Post::query()
            ->with('user')
            ->where('is_published', true)
            ->tap(fn (Builder $query) => $this->applySearch($query, $search, true))
            ->when($category, fn (Builder $query, string $category) => $query->where('category', $category))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateAll(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return Post::query()
            ->with('user')
            ->tap(fn (Builder $query) => $this->applySearch($query, $search))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function allForUser(User $user): Collection
    {
        return Post::query()->with('user')->where('user_id', $user->id)->orderBy('id')->get();
    }

    public function all(): Collection
    {
        return Post::query()->with('user')->orderBy('id')->get();
    }

    public function countForUser(User $user, ?bool $published = null): int
    {
        return Post::query()
            ->where('user_id', $user->id)
            ->when($published !== null, fn (Builder $query) => $query->where('is_published', $published))
            ->count();
    }

    public function findPublishedBySlug(string $slug): ?Post
    {
        return Post::query()->with('user')->where('slug', $slug)->where('is_published', true)->first();
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        return Post::query()
            ->where('slug', $slug)
            ->when($exceptId !== null, fn (Builder $query) => $query->where('id', '!=', $exceptId))
            ->exists();
    }

    public function create(array $data): Post
    {
        return Post::query()->create($data);
    }

    public function update(Post $post, array $data): Post
    {
        $post->update($data);

        return $post->refresh();
    }

    public function delete(Post $post): bool
    {
        return (bool) $post->delete();
    }

    /**
     * LIKE case-insensitive (ILIKE di PostgreSQL) pada judul, dan opsional pada isi/ringkasan.
     */
    private function applySearch(Builder $query, ?string $search, bool $withContent = false): void
    {
        if ($search === null || $search === '') {
            return;
        }

        $operator = $query->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $term = '%'.$search.'%';

        $query->where(function (Builder $q) use ($operator, $term, $withContent) {
            $q->where('title', $operator, $term);

            if ($withContent) {
                $q->orWhere('excerpt', $operator, $term)->orWhere('content', $operator, $term);
            }
        });
    }
}
