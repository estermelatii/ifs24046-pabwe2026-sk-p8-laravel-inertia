<?php

namespace Tests\Unit\Services;

use App\Features\Admin\Services\AdminUserService;
use App\Features\Post\Models\Post;
use App\Features\Post\Repositories\PostRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): AdminUserService
    {
        return new AdminUserService(new PostRepository);
    }

    public function test_users_with_counts_and_search_on_each_field(): void
    {
        $u = User::factory()->create(['name' => 'Budi Santoso', 'username' => 'budi_s', 'email' => 'budi@mail.test']);
        User::factory()->create(['name' => 'Cici', 'username' => 'cici', 'email' => 'cici@mail.test']);
        Post::factory()->for($u)->count(3)->create();

        $this->assertSame(2, $this->service()->users()->total());
        $this->assertSame(3, $this->service()->users('santoso')->first()->posts_count);
        $this->assertSame(1, $this->service()->users('budi_s')->total());
        $this->assertSame(1, $this->service()->users('cici@mail')->total());
    }

    public function test_posts_of_user(): void
    {
        $u = User::factory()->create();
        Post::factory()->for($u)->published()->create(['title' => 'Kopi']);
        Post::factory()->for($u)->draft()->create(['title' => 'Teh']);
        Post::factory()->create();

        $this->assertSame(2, $this->service()->postsOf($u)->total());
        $this->assertSame(1, $this->service()->postsOf($u, 'kopi')->total());
        $this->assertSame(1, $this->service()->postsOf($u, null, 'draft')->total());
    }
}
