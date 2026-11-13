<?php

namespace Tests\Unit\Services;

use App\Features\Post\Models\Post;
use App\Features\Post\Repositories\PostRepository;
use App\Features\Post\Services\PostExcelService;
use App\Features\Post\Services\PostService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Tests\TestCase;

class PostExcelServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): PostExcelService
    {
        return new PostExcelService(new PostService(new PostRepository));
    }

    public function test_export_contains_header_and_rows(): void
    {
        $user = User::factory()->create(['username' => 'penulis']);
        Post::factory()->for($user)->published()->create(['title' => 'Eksport', 'category' => 'Bisnis']);
        Post::factory()->for($user)->draft()->create();

        $binary = $this->service()->export(Post::with('user')->orderBy('id')->get());

        $path = tempnam(sys_get_temp_dir(), 'exp');
        file_put_contents($path, $binary);
        $rows = IOFactory::load($path)->getActiveSheet()->toArray();
        unlink($path);

        $this->assertSame(PostExcelService::HEADERS, $rows[0]);
        $this->assertSame(['Eksport', 'Bisnis'], [$rows[1][0], $rows[1][1]]);
        $this->assertSame('published', $rows[1][4]);
        $this->assertSame('draft', $rows[2][4]);
        $this->assertSame('penulis', $rows[1][5]);
    }

    public function test_import_skips_incomplete_rows_and_applies_defaults(): void
    {
        $user = User::factory()->create();
        $sheet = new Spreadsheet;
        $sheet->getActiveSheet()->fromArray([
            PostExcelService::HEADERS,
            ['Lengkap', 'Opini', 'ringkasan', 'isi lengkap', 'Published', ''],
            ['Minimal', '', '', 'isi', '', ''],
            ['', 'Umum', '', 'tanpa judul', 'draft', ''],
            ['Tanpa Isi', 'Umum', '', '', 'draft', ''],
        ], null, 'A1');
        $path = tempnam(sys_get_temp_dir(), 'imp');
        IOFactory::createWriter($sheet, 'Xlsx')->save($path);

        $count = $this->service()->import($user, $path);
        unlink($path);

        $this->assertSame(2, $count);
        $full = Post::where('title', 'Lengkap')->firstOrFail();
        $this->assertSame('Opini', $full->category->value);
        $this->assertTrue($full->is_published);
        $this->assertSame('ringkasan', $full->excerpt);

        $min = Post::where('title', 'Minimal')->firstOrFail();
        $this->assertSame('Umum', $min->category->value);
        $this->assertFalse($min->is_published);
        $this->assertNull($min->excerpt);
    }
}
