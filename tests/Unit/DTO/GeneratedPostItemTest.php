<?php

namespace Tests\Unit\DTO;

use App\Features\Ai\DTO\GeneratedPostItem;
use PHPUnit\Framework\TestCase;

class GeneratedPostItemTest extends TestCase
{
    public function test_from_array_maps_fields(): void
    {
        $item = GeneratedPostItem::fromArray(['title' => ' Judul ', 'category' => 'Bisnis', 'excerpt' => ' Ringkas ', 'content' => ' Isi ']);

        $this->assertSame(['title' => 'Judul', 'category' => 'Bisnis', 'excerpt' => 'Ringkas', 'content' => 'Isi'], $item->toArray());
    }

    public function test_defaults_for_missing_or_invalid_values(): void
    {
        $item = GeneratedPostItem::fromArray(['category' => 'Tidak Ada']);

        $this->assertSame('Artikel tanpa judul', $item->title);
        $this->assertSame('Umum', $item->category);
        $this->assertNull($item->excerpt);
        $this->assertSame('-', $item->content);
    }
}
