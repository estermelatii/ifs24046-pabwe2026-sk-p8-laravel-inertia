<?php

namespace Tests\Unit\Models;

use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_casts_and_relations(): void
    {
        $post = Post::factory()->published()->create(['category' => 'Gaya Hidup']);

        $this->assertSame(PostCategory::GayaHidup, $post->category);
        $this->assertTrue($post->is_published);
        $this->assertInstanceOf(User::class, $post->user);
    }

    public function test_cover_image_url(): void
    {
        Storage::fake('public');

        $this->assertNull(Post::factory()->create()->coverImageUrl());
        $this->assertStringContainsString('covers/a.jpg', Post::factory()->create(['cover_image_path' => 'covers/a.jpg'])->coverImageUrl());
    }

    public function test_list_item_and_detail_shape(): void
    {
        $post = Post::factory()->published()->create(['title' => 'Judul', 'slug' => 'judul', 'content' => 'Isi', 'category' => 'Opini']);

        $list = $post->toListItem();
        $this->assertSame('judul', $list['slug']);
        $this->assertSame('Opini', $list['category']);
        $this->assertSame($post->user->name, $list['author']);
        $this->assertArrayNotHasKey('content', $list);

        $detail = $post->toDetail();
        $this->assertSame('Isi', $detail['content']);
        $this->assertSame('Opini & Pemikiran', $detail['category_description']);
    }

    public function test_list_item_for_draft_has_null_published_at(): void
    {
        $this->assertNull(Post::factory()->draft()->create()->toListItem()['published_at']);
    }

    public function test_user_helpers(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->member()->create();
        Post::factory()->for($member)->count(2)->create();

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isMember());
        $this->assertTrue($member->isMember());
        $this->assertCount(2, $member->posts);
        $this->assertNull($member->profilePhotoUrl());
    }

    public function test_deleting_user_cascades_posts(): void
    {
        $user = User::factory()->create();
        Post::factory()->for($user)->create();

        $user->delete();

        $this->assertSame(0, Post::count());
    }
}
