<?php

namespace App\Features\Post\Services;

use App\Features\Post\Enums\PostCategory;
use App\Models\User;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class PostExcelService
{
    public const HEADERS = ['title', 'category', 'excerpt', 'content', 'status', 'author'];

    public function __construct(private readonly PostService $service) {}

    /**
     * Hasilkan isi file .xlsx (binary) dari koleksi artikel.
     */
    public function export(Collection $posts): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Posts');
        $sheet->fromArray(self::HEADERS, null, 'A1');

        $row = 2;
        foreach ($posts as $post) {
            $sheet->fromArray([
                $post->title,
                $post->category->value,
                $post->excerpt,
                $post->content,
                $post->is_published ? 'published' : 'draft',
                $post->user?->username,
            ], null, 'A'.$row);
            $row++;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'pst');
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($tmp);
        $content = (string) file_get_contents($tmp);
        unlink($tmp);

        return $content;
    }

    /**
     * Impor baris dari file xlsx menjadi artikel milik user. Mengembalikan jumlah baris yang tersimpan.
     */
    public function import(User $user, string $path): int
    {
        // Reader Xlsx dipakai langsung: IOFactory::load() menerima file teks apa pun sebagai CSV.
        $rows = (new XlsxReader)->load($path)->getActiveSheet()->toArray(null, true, true, false);
        array_shift($rows);

        $count = 0;
        foreach ($rows as $row) {
            $title = trim((string) ($row[0] ?? ''));
            $content = trim((string) ($row[3] ?? ''));

            if ($title === '' || $content === '') {
                continue;
            }

            $excerpt = trim((string) ($row[2] ?? ''));

            $this->service->create($user, [
                'title' => $title,
                'category' => (PostCategory::tryFrom((string) ($row[1] ?? '')) ?? PostCategory::Umum)->value,
                'excerpt' => $excerpt !== '' ? $excerpt : null,
                'content' => $content,
                'is_published' => strtolower(trim((string) ($row[4] ?? ''))) === 'published',
            ]);
            $count++;
        }

        return $count;
    }
}
