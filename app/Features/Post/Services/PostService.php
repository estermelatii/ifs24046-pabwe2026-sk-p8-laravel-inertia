<?php

namespace App\Features\Post\Services;

use App\Features\Ai\DTO\GeneratedPostItem;
use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostService
{
    public function __construct(private readonly PostRepositoryInterface $posts) {}

    public function listForUser(User $user, ?string $search = null, ?string $status = null, int $perPage = 10): LengthAwarePaginator
    {
        return $this->posts->paginateForUser($user, $search, $status, $perPage);
    }

    public function listPublished(?string $search = null, ?string $category = null, int $perPage = 9): LengthAwarePaginator
    {
        return $this->posts->paginatePublished($search, $category, $perPage);
    }

    public function findPublished(string $slug): ?Post
    {
        return $this->posts->findPublishedBySlug($slug);
    }

    /**
     * @return array{total:int, published:int, draft:int}
     */
    public function summary(User $user): array
    {
        return [
            'total' => $this->posts->countForUser($user),
            'published' => $this->posts->countForUser($user, true),
            'draft' => $this->posts->countForUser($user, false),
        ];
    }

    public function create(User $user, array $data): Post
    {
        $published = (bool) ($data['is_published'] ?? false);

        return $this->posts->create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['title']),
            'category' => $data['category'],
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'],
            'is_published' => $published,
            'published_at' => $published ? now() : null,
        ]);
    }

    /**
     * Slug tidak diubah saat judul berubah agar URL artikel yang sudah dibagikan tetap valid.
     */
    public function update(Post $post, array $data): Post
    {
        $published = (bool) ($data['is_published'] ?? false);

        return $this->posts->update($post, [
            'title' => $data['title'],
            'category' => $data['category'],
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'],
            'is_published' => $published,
            'published_at' => $this->publishedAt($post, $published),
        ]);
    }

    public function togglePublish(Post $post): Post
    {
        $published = ! $post->is_published;

        return $this->posts->update($post, [
            'is_published' => $published,
            'published_at' => $this->publishedAt($post, $published),
        ]);
    }

    public function delete(Post $post): bool
    {
        $this->deleteCoverFile($post);

        return $this->posts->delete($post);
    }

    public function storeCover(Post $post, UploadedFile $file): Post
    {
        $this->deleteCoverFile($post);

        return $this->posts->update($post, ['cover_image_path' => $file->store('post-covers', 'public')]);
    }

    public function removeCover(Post $post): Post
    {
        $this->deleteCoverFile($post);

        return $this->posts->update($post, ['cover_image_path' => null]);
    }

    /**
     * Simpan hasil AI sebagai draf milik user. Mengembalikan jumlah artikel yang dibuat.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    public function createFromGenerated(User $user, array $items): int
    {
        foreach ($items as $item) {
            $dto = GeneratedPostItem::fromArray($item);

            $this->create($user, $dto->toArray() + ['is_published' => false]);
        }

        return count($items);
    }

    public function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'artikel';
        $slug = $base;
        $suffix = 2;

        while ($this->posts->slugExists($slug)) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    /** Waktu publikasi pertama dipertahankan; draf mengosongkannya. */
    private function publishedAt(Post $post, bool $published): mixed
    {
        if (! $published) {
            return null;
        }

        return $post->published_at ?? now();
    }

    private function deleteCoverFile(Post $post): void
    {
        if ($post->cover_image_path) {
            Storage::disk('public')->delete($post->cover_image_path);
        }
    }
}
