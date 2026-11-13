<?php

namespace Tests\Feature\Admin;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    private function rows(string $content): array
    {
        $path = tempnam(sys_get_temp_dir(), 'adm');
        file_put_contents($path, $content);
        $rows = IOFactory::load($path)->getActiveSheet()->toArray();
        unlink($path);

        return $rows;
    }

    public function test_users_page_lists_users_with_post_counts_and_searches(): void
    {
        $writer = User::factory()->create(['name' => 'Penulis Satu', 'username' => 'penulis_satu']);
        Post::factory()->for($writer)->count(2)->create();

        $this->actingAs($this->admin)->get('/admin/users')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Admin/UserList')
            ->has('users.data', 2));

        $this->actingAs($this->admin)->get('/admin/users?search=satu')->assertInertia(fn (Assert $page) => $page
            ->has('users.data', 1)
            ->where('users.data.0.posts_count', 2));
    }

    public function test_user_posts_page_is_read_only_listing_with_filters(): void
    {
        $writer = User::factory()->create();
        Post::factory()->for($writer)->published()->create(['title' => 'Kopi']);
        Post::factory()->for($writer)->draft()->create(['title' => 'Teh']);

        $this->actingAs($this->admin)->get("/admin/users/{$writer->id}/posts")->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Admin/UserPostList')
            ->where('owner.id', $writer->id)
            ->has('posts.data', 2));

        $this->actingAs($this->admin)->get("/admin/users/{$writer->id}/posts?status=draft&search=teh")
            ->assertInertia(fn (Assert $page) => $page->has('posts.data', 1));
    }

    public function test_all_posts_page_and_search(): void
    {
        Post::factory()->published()->create(['title' => 'Alpha']);
        Post::factory()->draft()->create(['title' => 'Beta']);

        $this->actingAs($this->admin)->get('/admin/posts')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Admin/PostList')
            ->has('posts.data', 2));
        $this->actingAs($this->admin)->get('/admin/posts?search=alpha')->assertInertia(fn (Assert $page) => $page->has('posts.data', 1));
    }

    public function test_export_all_posts(): void
    {
        $writer = User::factory()->create(['username' => 'pena']);
        Post::factory()->for($writer)->count(2)->create();

        $response = $this->actingAs($this->admin)->get('/admin/posts/export')->assertOk();
        $rows = $this->rows($response->getContent());

        $this->assertCount(3, $rows);
        $this->assertSame('pena', $rows[1][5]);
    }

    public function test_export_posts_of_one_user(): void
    {
        $writer = User::factory()->create(['username' => 'penulis_x']);
        Post::factory()->for($writer)->create();
        Post::factory()->create();

        $response = $this->actingAs($this->admin)->get("/admin/users/{$writer->id}/posts/export")->assertOk();

        $this->assertStringContainsString('artikel-penulis_x.xlsx', $response->headers->get('Content-Disposition'));
        $this->assertCount(2, $this->rows($response->getContent()));
    }

    public function test_member_cannot_access_admin_routes(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->get('/admin/users')->assertForbidden();
        $this->actingAs($member)->get('/admin/posts')->assertForbidden();
        $this->actingAs($member)->get("/admin/users/{$member->id}/posts")->assertForbidden();
    }
}
