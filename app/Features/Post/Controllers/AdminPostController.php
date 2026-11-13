<?php

namespace App\Features\Post\Controllers;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Models\Post;
use App\Features\Post\Services\PostExcelService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AdminPostController extends Controller
{
    public function __construct(private readonly PostRepositoryInterface $posts) {}

    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();

        return Inertia::render('Admin/PostList', [
            'posts' => $this->posts->paginateAll($search)->through(fn (Post $post) => $post->toListItem()),
            'filters' => ['search' => $search],
        ]);
    }

    public function export(PostExcelService $excel): HttpResponse
    {
        return $this->download($excel->export($this->posts->all()), 'semua-artikel.xlsx');
    }
}
