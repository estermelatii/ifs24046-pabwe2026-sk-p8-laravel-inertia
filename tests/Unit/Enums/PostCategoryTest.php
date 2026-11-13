<?php

namespace Tests\Unit\Enums;

use App\Features\Post\Enums\PostCategory;
use PHPUnit\Framework\TestCase;

class PostCategoryTest extends TestCase
{
    public function test_values_match_module(): void
    {
        $this->assertSame(['Teknologi', 'Pendidikan', 'Gaya Hidup', 'Bisnis', 'Opini', 'Umum'], PostCategory::values());
    }

    public function test_every_case_has_description(): void
    {
        $expected = [
            'Teknologi' => 'Teknologi & Koding',
            'Pendidikan' => 'Pendidikan & Tutorial',
            'Gaya Hidup' => 'Gaya Hidup & Produktivitas',
            'Bisnis' => 'Bisnis & Karir',
            'Opini' => 'Opini & Pemikiran',
            'Umum' => 'Umum',
        ];

        foreach (PostCategory::cases() as $case) {
            $this->assertSame($expected[$case->value], $case->description());
        }
    }
}
