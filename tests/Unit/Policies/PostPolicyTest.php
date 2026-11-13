<?php

namespace Tests\Unit\Policies;

use App\Features\Post\Models\Post;
use App\Features\Post\Policies\PostPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_manage_own_post(): void
    {
        $owner = User::factory()->create();
        $post = Post::factory()->for($owner)->create();
        $policy = new PostPolicy;

        $this->assertTrue($policy->view($owner, $post));
        $this->assertTrue($policy->create($owner));
        $this->assertTrue($policy->update($owner, $post));
        $this->assertTrue($policy->delete($owner, $post));
    }

    public function test_other_member_cannot_touch_post(): void
    {
        $post = Post::factory()->create();
        $other = User::factory()->create();
        $policy = new PostPolicy;

        $this->assertFalse($policy->view($other, $post));
        $this->assertFalse($policy->update($other, $post));
        $this->assertFalse($policy->delete($other, $post));
    }

    public function test_admin_is_read_only(): void
    {
        $post = Post::factory()->create();
        $admin = User::factory()->admin()->create();
        $policy = new PostPolicy;

        $this->assertTrue($policy->view($admin, $post));
        $this->assertFalse($policy->create($admin));
        $this->assertFalse($policy->update($admin, $post));
        $this->assertFalse($policy->delete($admin, $post));
    }
}
