<?php

namespace Tests\Feature\Post;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

class MemberPostTest extends TestCase
{
    use RefreshDatabase;

    private User $member;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->member = User::factory()->create();
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'title' => 'Artikel Baru',
            'category' => 'Teknologi',
            'excerpt' => 'Ringkas',
            'content' => '## Halo',
            'is_published' => true,
        ], $override);
    }

    public function test_index_lists_own_posts_with_summary(): void
    {
        Post::factory()->for($this->member)->published()->count(2)->create();
        Post::factory()->for($this->member)->draft()->create();
        Post::factory()->published()->create(); // milik orang lain

        $this->actingAs($this->member)->get('/dashboard/posts')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Post/Index')
            ->has('posts.data', 3)
            ->where('summary.total', 3)
            ->where('summary.published', 2)
            ->where('summary.draft', 1)
            ->where('generated', null));
    }

    public function test_index_filters_by_search_and_status(): void
    {
        Post::factory()->for($this->member)->published()->create(['title' => 'Kopi Pagi']);
        Post::factory()->for($this->member)->draft()->create(['title' => 'Teh Sore']);

        $this->actingAs($this->member)->get('/dashboard/posts?search=kopi')->assertInertia(fn (Assert $page) => $page->has('posts.data', 1));
        $this->actingAs($this->member)->get('/dashboard/posts?status=draft')->assertInertia(fn (Assert $page) => $page->has('posts.data', 1)->where('posts.data.0.title', 'Teh Sore'));
        $this->actingAs($this->member)->get('/dashboard/posts?status=published')->assertInertia(fn (Assert $page) => $page->has('posts.data', 1)->where('posts.data.0.title', 'Kopi Pagi'));
    }

    public function test_store_creates_published_post_with_unique_slug(): void
    {
        $this->actingAs($this->member)->post('/dashboard/posts', $this->payload())->assertRedirect('/dashboard/posts');
        $this->actingAs($this->member)->post('/dashboard/posts', $this->payload(['is_published' => false]))->assertRedirect('/dashboard/posts');

        $first = Post::where('slug', 'artikel-baru')->firstOrFail();
        $second = Post::where('slug', 'artikel-baru-2')->firstOrFail();

        $this->assertTrue($first->is_published);
        $this->assertNotNull($first->published_at);
        $this->assertFalse($second->is_published);
        $this->assertNull($second->published_at);
        $this->assertSame($this->member->id, $first->user_id);
    }

    public function test_store_validates_input(): void
    {
        $this->actingAs($this->member)->post('/dashboard/posts', ['category' => 'Tidak Ada'])
            ->assertSessionHasErrors(['title', 'category', 'content']);
    }

    public function test_show_displays_detail_and_edit_flag(): void
    {
        $post = Post::factory()->for($this->member)->create();

        $this->actingAs($this->member)->get("/dashboard/posts/{$post->id}")->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Post/Show')
            ->where('post.id', $post->id)
            ->where('openEdit', false));

        $this->actingAs($this->member)->get("/dashboard/posts/{$post->id}?edit=1")
            ->assertInertia(fn (Assert $page) => $page->where('openEdit', true));
    }

    public function test_other_members_post_is_forbidden_and_missing_is_404(): void
    {
        $other = Post::factory()->create();

        $this->actingAs($this->member)->get("/dashboard/posts/{$other->id}")->assertForbidden();
        $this->actingAs($this->member)->put("/dashboard/posts/{$other->id}", $this->payload())->assertForbidden();
        $this->actingAs($this->member)->delete("/dashboard/posts/{$other->id}")->assertForbidden();
        $this->actingAs($this->member)->get('/dashboard/posts/999999')->assertNotFound();
    }

    public function test_admin_cannot_use_member_routes(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/dashboard/posts')->assertForbidden();
    }

    public function test_update_changes_fields_keeps_slug_and_first_publish_time(): void
    {
        $post = Post::factory()->for($this->member)->published()->create(['slug' => 'slug-tetap']);
        $publishedAt = $post->published_at;
        $this->travel(1)->days();

        $this->actingAs($this->member)->put("/dashboard/posts/{$post->id}", $this->payload(['title' => 'Judul Baru']))->assertRedirect();

        $post->refresh();
        $this->assertSame('Judul Baru', $post->title);
        $this->assertSame('slug-tetap', $post->slug);
        $this->assertTrue($post->published_at->equalTo($publishedAt));
    }

    public function test_update_to_draft_clears_published_at(): void
    {
        $post = Post::factory()->for($this->member)->published()->create();

        $this->actingAs($this->member)->put("/dashboard/posts/{$post->id}", $this->payload(['is_published' => false]));

        $this->assertNull($post->refresh()->published_at);
        $this->assertFalse($post->is_published);
    }

    public function test_toggle_publish_switches_status_both_ways(): void
    {
        $post = Post::factory()->for($this->member)->draft()->create();

        $this->actingAs($this->member)->patch("/dashboard/posts/{$post->id}/publish")->assertSessionHas('success', 'Artikel dipublikasikan.');
        $this->assertTrue($post->refresh()->is_published);
        $this->assertNotNull($post->published_at);

        $this->actingAs($this->member)->patch("/dashboard/posts/{$post->id}/publish")->assertSessionHas('success', 'Artikel dijadikan draf.');
        $this->assertFalse($post->refresh()->is_published);
    }

    public function test_destroy_deletes_post_and_cover_file(): void
    {
        $post = Post::factory()->for($this->member)->create();
        $this->actingAs($this->member)->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->image('c.jpg')]);
        $path = $post->refresh()->cover_image_path;
        Storage::disk('public')->assertExists($path);

        $this->actingAs($this->member)->delete("/dashboard/posts/{$post->id}")->assertRedirect('/dashboard/posts');

        $this->assertModelMissing($post);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_destroy_post_without_cover(): void
    {
        $post = Post::factory()->for($this->member)->create();

        $this->actingAs($this->member)->delete("/dashboard/posts/{$post->id}")->assertRedirect('/dashboard/posts');
        $this->assertModelMissing($post);
    }

    public function test_cover_upload_replace_and_remove(): void
    {
        $post = Post::factory()->for($this->member)->create();

        $this->actingAs($this->member)->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->image('a.jpg')]);
        $first = $post->refresh()->cover_image_path;
        Storage::disk('public')->assertExists($first);
        $this->assertNotNull($post->coverImageUrl());

        $this->actingAs($this->member)->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->image('b.png')]);
        $second = $post->refresh()->cover_image_path;
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);

        $this->actingAs($this->member)->delete("/dashboard/posts/{$post->id}/cover")->assertRedirect();
        $this->assertNull($post->refresh()->cover_image_path);
        Storage::disk('public')->assertMissing($second);
    }

    public function test_cover_must_be_an_image(): void
    {
        $post = Post::factory()->for($this->member)->create();

        $this->actingAs($this->member)->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->create('x.pdf', 10)])
            ->assertSessionHasErrors('cover');
    }

    public function test_export_downloads_own_posts_only(): void
    {
        Post::factory()->for($this->member)->create(['title' => 'Milik Saya']);
        Post::factory()->create(['title' => 'Milik Orang']);

        $response = $this->actingAs($this->member)->get('/dashboard/posts/export')->assertOk();
        $this->assertStringContainsString('attachment; filename="artikel-saya.xlsx"', $response->headers->get('Content-Disposition'));

        $path = tempnam(sys_get_temp_dir(), 'dl');
        file_put_contents($path, $response->getContent());
        $rows = IOFactory::load($path)->getActiveSheet()->toArray();
        unlink($path);

        $this->assertCount(2, $rows);
        $this->assertSame('Milik Saya', $rows[1][0]);
    }

    public function test_import_creates_posts_from_excel(): void
    {
        $sheet = new Spreadsheet;
        $sheet->getActiveSheet()->fromArray([
            ['title', 'category', 'excerpt', 'content', 'status', 'author'],
            ['Impor Satu', 'Bisnis', 'ringkas', 'isi satu', 'published', ''],
            ['Impor Dua', 'Tidak Valid', '', 'isi dua', 'draft', ''],
            ['', 'Umum', '', 'tanpa judul', 'draft', ''],
        ], null, 'A1');
        $path = tempnam(sys_get_temp_dir(), 'imp');
        IOFactory::createWriter($sheet, 'Xlsx')->save($path);
        $file = new UploadedFile($path, 'artikel.xlsx', null, null, true);

        $this->actingAs($this->member)->post('/dashboard/posts/import', ['file' => $file])
            ->assertSessionHas('success', '2 artikel berhasil diimpor.');

        $this->assertSame('Bisnis', Post::where('title', 'Impor Satu')->firstOrFail()->category->value);
        $this->assertTrue(Post::where('title', 'Impor Satu')->firstOrFail()->is_published);
        $this->assertSame('Umum', Post::where('title', 'Impor Dua')->firstOrFail()->category->value);
        $this->assertNull(Post::where('title', 'Impor Dua')->firstOrFail()->excerpt);
    }

    public function test_import_rejects_unreadable_file(): void
    {
        $file = UploadedFile::fake()->createWithContent('rusak.xlsx', 'bukan excel');

        $this->actingAs($this->member)->post('/dashboard/posts/import', ['file' => $file])->assertSessionHasErrors('file');
    }

    public function test_import_requires_a_file(): void
    {
        $this->actingAs($this->member)->post('/dashboard/posts/import', [])->assertSessionHasErrors('file');
    }

    public function test_ai_generate_flashes_results(): void
    {
        config(['openai.api_key' => 'k', 'openai.base_url' => 'https://api.test/v1']);
        Http::fake(['api.test/*' => Http::response(['choices' => [['message' => ['content' => json_encode(['items' => [
            ['title' => 'Draf AI', 'category' => 'Opini', 'excerpt' => 'e', 'content' => '## c'],
        ]])]]]])]);

        $this->actingAs($this->member)->post('/dashboard/posts/ai-generate', ['prompt' => 'topik', 'count' => 1])
            ->assertSessionHas('generated', [['title' => 'Draf AI', 'category' => 'Opini', 'excerpt' => 'e', 'content' => '## c']]);
    }

    public function test_ai_generate_reports_failure_and_validates(): void
    {
        config(['openai.api_key' => null]);

        $this->actingAs($this->member)->post('/dashboard/posts/ai-generate', ['prompt' => 'topik', 'count' => 2])
            ->assertSessionHasErrors('prompt');
        $this->actingAs($this->member)->post('/dashboard/posts/ai-generate', ['prompt' => '', 'count' => 99])
            ->assertSessionHasErrors(['prompt', 'count']);
    }

    public function test_index_exposes_generated_results_from_session(): void
    {
        $this->actingAs($this->member)->withSession(['generated' => [['title' => 'X']]])->get('/dashboard/posts')
            ->assertInertia(fn (Assert $page) => $page->where('generated.0.title', 'X'));
    }

    public function test_ai_save_creates_drafts(): void
    {
        $this->actingAs($this->member)->post('/dashboard/posts/ai-save', ['items' => [
            ['title' => 'Satu', 'category' => 'Pendidikan', 'excerpt' => 'e', 'content' => 'c'],
            ['title' => 'Dua', 'category' => 'Aneh', 'content' => 'c'],
        ]])->assertRedirect('/dashboard/posts')->assertSessionHas('success', '2 draf artikel dari AI berhasil disimpan.');

        $this->assertSame(2, $this->member->posts()->where('is_published', false)->count());
        $this->assertSame('Umum', Post::where('title', 'Dua')->firstOrFail()->category->value);
    }

    public function test_ai_save_validates_items(): void
    {
        $this->actingAs($this->member)->post('/dashboard/posts/ai-save', ['items' => []])->assertSessionHasErrors('items');
    }
}
