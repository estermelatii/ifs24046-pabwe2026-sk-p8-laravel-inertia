<?php

namespace App\Features\Admin\Services;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminUserService
{
    public function __construct(private readonly PostRepositoryInterface $posts) {}

    public function users(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return User::query()
            ->withCount('posts')
            ->when($search, function ($query, $search) {
                $operator = $query->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
                $query->where(function ($q) use ($search, $operator) {
                    $q->where('name', $operator, "%{$search}%")
                        ->orWhere('username', $operator, "%{$search}%")
                        ->orWhere('email', $operator, "%{$search}%");
                });
            })
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function postsOf(User $user, ?string $search = null, ?string $status = null, int $perPage = 10): LengthAwarePaginator
    {
        return $this->posts->paginateForUser($user, $search, $status, $perPage);
    }
}
