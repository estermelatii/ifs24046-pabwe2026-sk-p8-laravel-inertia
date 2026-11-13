<?php

namespace App\Http\Middleware;

use App\Features\Post\Models\Post;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePostOwnership
{
    public function handle(Request $request, Closure $next): Response
    {
        $post = $request->route('post');

        if ($post !== null) {
            if (! $post instanceof Post) {
                $post = Post::query()->find($post);
            }

            abort_if($post === null, 404);
            abort_unless($request->user() && $post->user_id === $request->user()->id, 403, 'Artikel ini bukan milik Anda.');
        }

        return $next($request);
    }
}
