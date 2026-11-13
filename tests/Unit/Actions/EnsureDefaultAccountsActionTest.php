<?php

namespace Tests\Unit\Actions;

use App\Features\Auth\Actions\EnsureDefaultAccountsAction;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EnsureDefaultAccountsActionTest extends TestCase
{
    use RefreshDatabase;

    private function action(): EnsureDefaultAccountsAction
    {
        return $this->app->make(EnsureDefaultAccountsAction::class);
    }

    public function test_creates_admin_member_and_demo_posts_on_empty_database(): void
    {
        $this->action()->execute();

        $admin = User::where('username', 'admin')->firstOrFail();
        $member = User::where('username', 'member')->firstOrFail();

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue(Hash::check('admin', $admin->password));
        $this->assertTrue($member->isMember());
        $this->assertTrue(Hash::check('member', $member->password));
        $this->assertSame(3, Post::where('user_id', $member->id)->count());
        $this->assertSame(2, Post::where('is_published', true)->count());
    }

    public function test_is_idempotent(): void
    {
        $this->action()->execute();
        $this->action()->execute();

        $this->assertSame(2, User::count());
        $this->assertSame(3, Post::count());
    }

    public function test_repairs_existing_admin_role_and_password(): void
    {
        User::factory()->create(['username' => 'admin', 'role' => User::ROLE_MEMBER, 'password' => 'salah']);

        $this->action()->execute();

        $admin = User::where('username', 'admin')->firstOrFail();
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue(Hash::check('admin', $admin->password));
    }

    public function test_uses_alternative_email_when_default_is_taken(): void
    {
        User::factory()->create(['username' => 'orang_lain', 'email' => 'admin@example.com']);

        $this->action()->execute();

        $this->assertStringStartsWith('admin.', User::where('username', 'admin')->firstOrFail()->email);
    }

    public function test_existing_member_does_not_get_demo_posts(): void
    {
        User::factory()->create(['username' => 'member']);

        $this->action()->execute();

        $this->assertSame(0, Post::count());
    }

    public function test_seeder_runs_the_action(): void
    {
        $this->seed();

        $this->assertSame(2, User::count());
    }
}
