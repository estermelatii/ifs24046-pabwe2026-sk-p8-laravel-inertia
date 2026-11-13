<?php

namespace Tests\Unit\Providers;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\Services\OpenAiPostGeneratorService;
use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Models\Post;
use App\Features\Post\Policies\PostPolicy;
use App\Features\Post\Repositories\PostRepository;
use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    public function test_bindings(): void
    {
        $this->assertInstanceOf(PostRepository::class, $this->app->make(PostRepositoryInterface::class));
        $this->assertInstanceOf(OpenAiPostGeneratorService::class, $this->app->make(AiClientInterface::class));
    }

    public function test_policy_registered(): void
    {
        $this->assertInstanceOf(PostPolicy::class, Gate::getPolicyFor(Post::class));
    }

    public function test_rate_limiter_registered_for_guest_and_user(): void
    {
        $limiter = RateLimiter::limiter('1000-per-second');
        $this->assertNotNull($limiter);

        $request = \Illuminate\Http\Request::create('/');
        $this->assertNotNull($limiter($request));

        $request->setUserResolver(fn () => \App\Models\User::factory()->make(['id' => 5]));
        $this->assertNotNull($limiter($request));
    }

    public function test_force_https_when_enabled(): void
    {
        config(['app.force_https' => true]);
        (new AppServiceProvider($this->app))->boot();

        $this->assertStringStartsWith('https://', url('/x'));
    }
}
