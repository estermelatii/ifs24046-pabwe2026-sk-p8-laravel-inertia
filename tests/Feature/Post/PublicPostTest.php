<?php

namespace Tests\Feature\Post;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_is_public_and_lists_only_published_posts(): void
    {
        Post::factory()->published()->count(2)->create();
        Post::factory()->draft()->create();

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/PostIndex')
            ->where('isHome', true)
            ->has('posts.data', 2));
    }

    public function test_articles_page_is_not_home(): void
    {
        $this->get('/articles')->assertOk()->assertInertia(fn (Assert $page) => $page->where('isHome', false));
    }

    public function test_search_matches_title_excerpt_and_content(): void
    {
        Post::factory()->published()->create(['title' => 'Belajar Laravel', 'excerpt' => 'x', 'content' => 'y']);
        Post::factory()->published()->create(['title' => 'Lain', 'excerpt' => 'tentang kopi', 'content' => 'y']);
        Post::factory()->published()->create(['title' => 'Lagi', 'excerpt' => 'x', 'content' => 'resep teh']);
        Post::factory()->published()->create(['title' => 'Tak cocok', 'excerpt' => 'x', 'content' => 'y']);

        $this->get('/articles?search=laravel')->assertInertia(fn (Assert $page) => $page->has('posts.data', 1));
        $this->get('/articles?search=kopi')->assertInertia(fn (Assert $page) => $page->has('posts.data', 1));
        $this->get('/articles?search=teh')->assertInertia(fn (Assert $page) => $page->has('posts.data', 1));
    }

    public function test_category_filter(): void
    {
        Post::factory()->published()->create(['category' => 'Bisnis']);
        Post::factory()->published()->create(['category' => 'Opini']);

        $this->get('/articles?category=Bisnis')->assertInertia(fn (Assert $page) => $page
            ->has('posts.data', 1)
            ->where('filters.category', 'Bisnis'));
    }

    public function test_published_article_can_be_read_by_guest(): void
    {
        $post = Post::factory()->published()->create(['title' => 'Judul Terbit', 'slug' => 'judul-terbit', 'content' => '## Isi']);

        $this->get('/articles/judul-terbit')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/PostShow')
            ->where('post.title', 'Judul Terbit')
            ->where('post.content', '## Isi')
            ->where('post.id', $post->id));
    }

    public function test_draft_and_unknown_articles_return_404(): void
    {
        Post::factory()->draft()->create(['slug' => 'masih-draf']);

        $this->get('/articles/masih-draf')->assertNotFound();
        $this->get('/articles/tidak-ada')->assertNotFound();
    }

    public function test_article_shows_author_name(): void
    {
        $author = User::factory()->create(['name' => 'Penulis Hebat']);
        Post::factory()->for($author)->published()->create(['slug' => 'karya']);

        $this->get('/articles/karya')->assertInertia(fn (Assert $page) => $page->where('post.author', 'Penulis Hebat'));
    }
}
