<?php

namespace Tests\Unit\Middleware;

use App\Features\Post\Models\Post;
use App\Http\Middleware\EnsurePostOwnership;
use App\Http\Middleware\EnsureRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private function roleRequest(?User $user, string ...$roles): Response
    {
        $request = Request::create('/x');
        $request->setUserResolver(fn () => $user);

        return (new EnsureRole)->handle($request, fn () => new Response('ok'), ...$roles);
    }

    private function ownershipRequest(?User $user, mixed $postParam): Response
    {
        $request = Request::create('/dashboard/posts/1');
        $route = new Route('GET', $postParam === null ? '/dashboard/posts/1' : '/dashboard/posts/{post}', fn () => 'x');
        $route->bind($request);
        if ($postParam !== null) {
            $route->setParameter('post', $postParam);
        }
        $request->setRouteResolver(fn () => $route);
        $request->setUserResolver(fn () => $user);

        return (new EnsurePostOwnership)->handle($request, fn () => new Response('ok'));
    }

    public function test_role_allows_matching_role(): void
    {
        $this->assertSame('ok', $this->roleRequest(new User(['role' => 'admin']), 'admin')->getContent());
        $this->assertSame('ok', $this->roleRequest(new User(['role' => 'member']), 'admin', 'member')->getContent());
    }

    public function test_role_rejects_other_role_and_guest(): void
    {
        $this->expectException(HttpException::class);
        $this->roleRequest(new User(['role' => 'member']), 'admin');
    }

    public function test_role_rejects_guest(): void
    {
        $this->expectException(HttpException::class);
        $this->roleRequest(null, 'member');
    }

    public function test_ownership_allows_owner_with_model_or_id(): void
    {
        $owner = User::factory()->create();
        $post = Post::factory()->for($owner)->create();

        $this->assertSame('ok', $this->ownershipRequest($owner, $post)->getContent());
        $this->assertSame('ok', $this->ownershipRequest($owner, $post->id)->getContent());
    }

    public function test_ownership_passes_when_route_has_no_post(): void
    {
        $this->assertSame('ok', $this->ownershipRequest(User::factory()->create(), null)->getContent());
    }

    public function test_ownership_rejects_non_owner(): void
    {
        $post = Post::factory()->create();
        $this->expectException(HttpException::class);

        $this->ownershipRequest(User::factory()->create(), $post);
    }

    public function test_ownership_rejects_guest(): void
    {
        $post = Post::factory()->create();
        $this->expectException(HttpException::class);

        $this->ownershipRequest(null, $post);
    }

    public function test_ownership_404_for_missing_post(): void
    {
        $this->expectException(HttpException::class);

        $this->ownershipRequest(User::factory()->create(), 999999);
    }
}
