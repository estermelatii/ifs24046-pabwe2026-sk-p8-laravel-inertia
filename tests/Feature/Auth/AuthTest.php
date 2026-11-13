<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_rendered_for_guest(): void
    {
        $this->get('/login')->assertOk()->assertInertia(fn (Assert $page) => $page->component('Auth/Login'));
    }

    public function test_register_page_is_rendered_for_guest(): void
    {
        $this->get('/register')->assertOk()->assertInertia(fn (Assert $page) => $page->component('Auth/Register'));
    }

    public function test_member_login_redirects_to_dashboard(): void
    {
        User::factory()->create(['username' => 'penulis', 'password' => 'rahasia']);

        $this->post('/login', ['username' => 'penulis', 'password' => 'rahasia'])->assertRedirect('/dashboard/posts');
        $this->assertAuthenticated();
    }

    public function test_admin_login_redirects_to_admin_panel(): void
    {
        User::factory()->admin()->create(['username' => 'bos', 'password' => 'rahasia']);

        $this->post('/login', ['username' => 'bos', 'password' => 'rahasia'])->assertRedirect('/admin/users');
    }

    public function test_login_with_email_works(): void
    {
        User::factory()->create(['email' => 'a@example.com', 'password' => 'rahasia']);

        $this->post('/login', ['username' => 'a@example.com', 'password' => 'rahasia'])->assertRedirect('/dashboard/posts');
    }

    public function test_login_with_wrong_password_fails(): void
    {
        User::factory()->create(['username' => 'penulis', 'password' => 'rahasia']);

        $this->from('/login')->post('/login', ['username' => 'penulis', 'password' => 'salah'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_login_requires_fields(): void
    {
        $this->post('/login', [])->assertSessionHasErrors(['username', 'password']);
    }

    public function test_register_creates_member_and_logs_in(): void
    {
        $this->post('/register', [
            'name' => 'Penulis Baru',
            'username' => 'penulis_baru',
            'email' => 'baru@example.com',
            'password' => 'rahasia',
            'password_confirmation' => 'rahasia',
        ])->assertRedirect('/dashboard/posts');

        $user = User::where('username', 'penulis_baru')->firstOrFail();
        $this->assertTrue($user->isMember());
        $this->assertAuthenticatedAs($user);
    }

    public function test_register_validates_input(): void
    {
        User::factory()->create(['username' => 'dipakai', 'email' => 'dipakai@example.com']);

        $this->post('/register', [
            'name' => '', 'username' => 'dipakai', 'email' => 'dipakai@example.com',
            'password' => 'abc', 'password_confirmation' => 'xyz',
        ])->assertSessionHasErrors(['name', 'username', 'email', 'password']);
    }

    public function test_logout_redirects_home(): void
    {
        $this->actingAs(User::factory()->create())->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_authenticated_user_visiting_login_is_redirected_by_role(): void
    {
        $this->actingAs(User::factory()->create())->get('/login')->assertRedirect('/dashboard/posts');
        $this->actingAs(User::factory()->admin()->create())->get('/login')->assertRedirect('/admin/users');
    }

    public function test_guest_is_redirected_from_protected_routes(): void
    {
        $this->get('/dashboard/posts')->assertRedirect('/login');
        $this->get('/admin/users')->assertRedirect('/login');
        $this->get('/profile')->assertRedirect('/login');
    }

    public function test_shared_props_expose_user_and_categories(): void
    {
        $user = User::factory()->create(['name' => 'Si Penulis']);

        $this->actingAs($user)->get('/dashboard/posts')->assertInertia(fn (Assert $page) => $page
            ->where('auth.user.name', 'Si Penulis')
            ->where('auth.user.role', 'member')
            ->has('categories', 6));

        auth()->logout();
        $this->get('/')->assertInertia(fn (Assert $page) => $page->where('auth.user', null));
    }

    public function test_flash_messages_are_shared(): void
    {
        $this->withSession(['success' => 'Berhasil', 'error' => 'Gagal'])->get('/')
            ->assertInertia(fn (Assert $page) => $page->where('flash.success', 'Berhasil')->where('flash.error', 'Gagal'));
    }
}
