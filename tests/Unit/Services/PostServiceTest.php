<?php

namespace Tests\Unit\Services;

use App\Features\Post\Models\Post;
use App\Features\Post\Repositories\PostRepository;
use App\Features\Post\Services\PostService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): PostService
    {
        return new PostService(new PostRepository);
    }

    public function test_unique_slug_handles_collisions_and_empty_titles(): void
    {
        $service = $this->service();
        Post::factory()->create(['slug' => 'halo-dunia']);
        Post::factory()->create(['slug' => 'halo-dunia-2']);
        Post::factory()->create(['slug' => 'artikel']);

        $this->assertSame('halo-dunia-3', $service->uniqueSlug('Halo Dunia'));
        $this->assertSame('baru', $service->uniqueSlug('Baru'));
        $this->assertSame('artikel-2', $service->uniqueSlug('!!!'));
    }

    public function test_create_handles_missing_optional_fields(): void
    {
        $user = User::factory()->create();

        $post = $this->service()->create($user, ['title' => 'Minimal', 'category' => 'Umum', 'content' => 'isi']);

        $this->assertNull($post->excerpt);
        $this->assertFalse($post->is_published);
        $this->assertNull($post->published_at);
    }

    public function test_update_without_is_published_makes_draft(): void
    {
        $post = Post::factory()->published()->create();

        $updated = $this->service()->update($post, ['title' => 'T', 'category' => 'Umum', 'content' => 'c']);

        $this->assertFalse($updated->is_published);
        $this->assertNull($updated->excerpt);
    }

    public function test_publishing_a_draft_via_update_sets_published_at(): void
    {
        $post = Post::factory()->draft()->create();

        $updated = $this->service()->update($post, ['title' => 'T', 'category' => 'Umum', 'content' => 'c', 'is_published' => true]);

        $this->assertNotNull($updated->published_at);
    }

    public function test_summary_counts(): void
    {
        $user = User::factory()->create();
        Post::factory()->for($user)->published()->create();
        Post::factory()->for($user)->draft()->count(2)->create();

        $this->assertSame(['total' => 3, 'published' => 1, 'draft' => 2], $this->service()->summary($user));
    }

    public function test_list_and_find_delegate_to_repository(): void
    {
        $user = User::factory()->create();
        Post::factory()->for($user)->published()->create(['slug' => 'satu']);

        $this->assertSame(1, $this->service()->listForUser($user)->total());
        $this->assertSame(1, $this->service()->listPublished()->total());
        $this->assertNotNull($this->service()->findPublished('satu'));
    }

    public function test_cover_lifecycle(): void
    {
        Storage::fake('public');
        $post = Post::factory()->create();
        $service = $this->service();

        $post = $service->storeCover($post, UploadedFile::fake()->image('a.jpg'));
        Storage::disk('public')->assertExists($post->cover_image_path);

        $post = $service->removeCover($post);
        $this->assertNull($post->cover_image_path);
    }

    public function test_create_from_generated_creates_drafts_and_returns_count(): void
    {
        $user = User::factory()->create();

        $count = $this->service()->createFromGenerated($user, [
            ['title' => 'Satu', 'category' => 'Bisnis', 'excerpt' => 'e', 'content' => 'c'],
            ['title' => 'Satu', 'category' => 'Bisnis', 'content' => 'c'],
        ]);

        $this->assertSame(2, $count);
        $this->assertSame(['satu', 'satu-2'], $user->posts()->orderBy('id')->pluck('slug')->all());
        $this->assertSame(0, $user->posts()->where('is_published', true)->count());
    }
}
