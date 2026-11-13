<?php

namespace App\Features\Ai\DTO;

use App\Features\Post\Enums\PostCategory;

final readonly class GeneratedPostItem
{
    public function __construct(
        public string $title,
        public string $category = 'Umum',
        public ?string $excerpt = null,
        public string $content = '',
    ) {}

    public static function fromArray(array $data): self
    {
        $title = trim((string) ($data['title'] ?? ''));
        $category = PostCategory::tryFrom((string) ($data['category'] ?? '')) ?? PostCategory::Umum;
        $excerpt = trim((string) ($data['excerpt'] ?? ''));

        return new self(
            title: $title !== '' ? $title : 'Artikel tanpa judul',
            category: $category->value,
            excerpt: $excerpt !== '' ? $excerpt : null,
            content: trim((string) ($data['content'] ?? '')) ?: '-',
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'category' => $this->category,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
        ];
    }
}
