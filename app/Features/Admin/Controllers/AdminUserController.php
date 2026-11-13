<?php

namespace App\Features\Admin\Controllers;

use App\Features\Admin\Services\AdminUserService;
use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Models\Post;
use App\Features\Post\Services\PostExcelService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AdminUserController extends Controller
{
    public function __construct(private readonly AdminUserService $service) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();

        return Inertia::render('Admin/UserList', [
            'users' => $this->service->users($search)->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'posts_count' => $user->posts_count,
                'created_at' => $user->created_at?->toIso8601String(),
            ]),
            'filters' => ['search' => $search],
        ]);
    }

    public function posts(Request $request, User $user): Response
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();

        return Inertia::render('Admin/UserPostList', [
            'owner' => ['id' => $user->id, 'name' => $user->name, 'username' => $user->username],
            'posts' => $this->service->postsOf($user, $search, $status)->through(fn (Post $post) => $post->toListItem()),
            'filters' => ['search' => $search, 'status' => $status],
        ]);
    }

    public function exportPosts(User $user, PostExcelService $excel, PostRepositoryInterface $repository): HttpResponse
    {
        return $this->download($excel->export($repository->allForUser($user)), "artikel-{$user->username}.xlsx");
    }
}
