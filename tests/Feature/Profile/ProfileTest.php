<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_renders(): void
    {
        $this->actingAs(User::factory()->create())->get('/profile')->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Profile/Edit'));
    }

    public function test_update_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/profile', ['name' => 'Nama Baru', 'username' => 'nama_baru', 'email' => 'baru@example.com'])
            ->assertSessionHas('success');

        $this->assertSame('nama_baru', $user->refresh()->username);
    }

    public function test_update_profile_validates_uniqueness_but_allows_own_values(): void
    {
        $other = User::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->put('/profile', ['name' => 'X', 'username' => $other->username, 'email' => $other->email])
            ->assertSessionHasErrors(['username', 'email']);

        $this->actingAs($user)->put('/profile', ['name' => 'X', 'username' => $user->username, 'email' => $user->email])
            ->assertSessionHasNoErrors();
    }

    public function test_change_password(): void
    {
        $user = User::factory()->create(['password' => 'lama123']);

        $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'lama123', 'password' => 'baru1234', 'password_confirmation' => 'baru1234',
        ])->assertSessionHas('success');

        $this->assertTrue(Hash::check('baru1234', $user->refresh()->password));
    }

    public function test_change_password_with_wrong_current_password(): void
    {
        $user = User::factory()->create(['password' => 'lama123']);

        $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'salah', 'password' => 'baru1234', 'password_confirmation' => 'baru1234',
        ])->assertSessionHasErrors('current_password');
    }

    public function test_upload_photo_replaces_previous_one(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->post('/profile/photo', ['photo' => UploadedFile::fake()->image('a.jpg')])->assertSessionHas('success');
        $first = $user->refresh()->profile_photo_path;
        Storage::disk('public')->assertExists($first);
        $this->assertNotNull($user->profilePhotoUrl());

        $this->actingAs($user)->post('/profile/photo', ['photo' => UploadedFile::fake()->image('b.png')]);
        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($user->refresh()->profile_photo_path);
    }

    public function test_photo_must_be_image(): void
    {
        $this->actingAs(User::factory()->create())->post('/profile/photo', ['photo' => UploadedFile::fake()->create('x.pdf', 5)])
            ->assertSessionHasErrors('photo');
    }
}
