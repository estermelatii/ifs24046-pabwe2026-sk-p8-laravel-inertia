<?php

namespace App\Features\Post\Models;

use App\Features\Post\Enums\PostCategory;
use App\Models\User;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'slug', 'category', 'excerpt', 'content',
        'cover_image_path', 'is_published', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'category' => PostCategory::class,
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function newFactory(): PostFactory
    {
        return PostFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coverImageUrl(): ?string
    {
        return $this->cover_image_path
            ? Storage::disk('public')->url($this->cover_image_path)
            : null;
    }

    /**
     * Representasi ringkas untuk daftar (tanpa isi artikel).
     *
     * @return array<string, mixed>
     */
    public function toListItem(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'category' => $this->category->value,
            'excerpt' => $this->excerpt,
            'cover_url' => $this->coverImageUrl(),
            'is_published' => $this->is_published,
            'published_at' => $this->published_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'author' => $this->user?->name,
            'author_username' => $this->user?->username,
        ];
    }

    /**
     * Representasi lengkap untuk halaman detail / pembaca.
     *
     * @return array<string, mixed>
     */
    public function toDetail(): array
    {
        return $this->toListItem() + [
            'content' => $this->content,
            'category_description' => $this->category->description(),
        ];
    }
}
