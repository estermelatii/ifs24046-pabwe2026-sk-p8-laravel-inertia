<?php

namespace Tests\Unit\Repositories;

use App\Features\Post\Models\Post;
use App\Features\Post\Repositories\PostRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PostRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new PostRepository;
    }

    public function test_paginate_for_user_filters_owner_search_and_status(): void
    {
        $user = User::factory()->create();
        Post::factory()->for($user)->published()->create(['title' => 'Kopi']);
        Post::factory()->for($user)->draft()->create(['title' => 'Teh']);
        Post::factory()->create(['title' => 'Kopi Orang Lain']);

        $this->assertSame(2, $this->repo->paginateForUser($user)->total());
        $this->assertSame(1, $this->repo->paginateForUser($user, 'kopi')->total());
        $this->assertSame(1, $this->repo->paginateForUser($user, null, 'draft')->total());
        $this->assertSame(1, $this->repo->paginateForUser($user, '', 'published')->total());
    }

    public function test_paginate_published_only_published_with_search_and_category(): void
    {
        Post::factory()->published()->create(['title' => 'A', 'category' => 'Bisnis', 'content' => 'rahasia dapur']);
        Post::factory()->published()->create(['title' => 'B', 'category' => 'Opini']);
        Post::factory()->draft()->create(['category' => 'Bisnis']);

        $this->assertSame(2, $this->repo->paginatePublished()->total());
        $this->assertSame(1, $this->repo->paginatePublished(null, 'Bisnis')->total());
        $this->assertSame(1, $this->repo->paginatePublished('dapur')->total());
    }

    public function test_paginate_published_orders_by_latest_publication(): void
    {
        $old = Post::factory()->create(['is_published' => true, 'published_at' => now()->subDays(3)]);
        $new = Post::factory()->create(['is_published' => true, 'published_at' => now()]);

        $ids = collect($this->repo->paginatePublished()->items())->pluck('id')->all();

        $this->assertSame([$new->id, $old->id], $ids);
    }

    public function test_paginate_all_and_all(): void
    {
        Post::factory()->published()->create(['title' => 'Alpha']);
        Post::factory()->draft()->create(['title' => 'Beta']);

        $this->assertSame(2, $this->repo->paginateAll()->total());
        $this->assertSame(1, $this->repo->paginateAll('alpha')->total());
        $this->assertCount(2, $this->repo->all());
    }

    public function test_all_for_user_and_counts(): void
    {
        $user = User::factory()->create();
        Post::factory()->for($user)->published()->count(2)->create();
        Post::factory()->for($user)->draft()->create();
        Post::factory()->create();

        $this->assertCount(3, $this->repo->allForUser($user));
        $this->assertSame(3, $this->repo->countForUser($user));
        $this->assertSame(2, $this->repo->countForUser($user, true));
        $this->assertSame(1, $this->repo->countForUser($user, false));
    }

    public function test_find_published_by_slug(): void
    {
        Post::factory()->published()->create(['slug' => 'terbit']);
        Post::factory()->draft()->create(['slug' => 'draf']);

        $this->assertNotNull($this->repo->findPublishedBySlug('terbit'));
        $this->assertNull($this->repo->findPublishedBySlug('draf'));
        $this->assertNull($this->repo->findPublishedBySlug('tidak-ada'));
    }

    public function test_slug_exists_with_exception(): void
    {
        $post = Post::factory()->create(['slug' => 'ada']);

        $this->assertTrue($this->repo->slugExists('ada'));
        $this->assertFalse($this->repo->slugExists('ada', $post->id));
        $this->assertFalse($this->repo->slugExists('belum'));
    }

    public function test_create_update_delete(): void
    {
        $user = User::factory()->create();
        $post = $this->repo->create(['user_id' => $user->id, 'title' => 'T', 'slug' => 't', 'category' => 'Umum', 'content' => 'c']);

        $this->assertSame('Baru', $this->repo->update($post, ['title' => 'Baru'])->title);
        $this->assertTrue($this->repo->delete($post));
        $this->assertModelMissing($post);
    }
}
