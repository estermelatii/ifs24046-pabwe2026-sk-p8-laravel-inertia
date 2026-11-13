<?php

namespace App\Features\Ai\Services;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedPostItem;
use App\Features\Post\Enums\PostCategory;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiPostGeneratorService implements AiClientInterface
{
    public const MAX_COUNT = 5;

    public function generate(string $prompt, int $count = 3): array
    {
        $apiKey = config('openai.api_key');

        if (! $apiKey) {
            throw new RuntimeException('OPENAI_KEY belum dikonfigurasi.');
        }

        $count = max(1, min(self::MAX_COUNT, $count));
        $categories = implode(', ', PostCategory::values());

        $response = Http::withToken($apiKey)
            ->timeout((int) config('openai.timeout'))
            ->post(rtrim((string) config('openai.base_url'), '/').'/chat/completions', [
                'model' => config('openai.model'),
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Anda penulis blog. Balas HANYA JSON berbentuk {"items":[{"title","category","excerpt","content"}]}. '
                            ."Hasilkan tepat {$count} artikel dalam bahasa Indonesia. category salah satu dari: {$categories}. "
                            .'excerpt 1-2 kalimat. content berupa Markdown (gunakan sub-judul, paragraf, dan daftar).',
                    ],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Permintaan ke OpenAI gagal: HTTP '.$response->status());
        }

        $content = $response->json('choices.0.message.content');
        $decoded = is_string($content) ? json_decode($content, true) : null;

        if (! is_array($decoded) || ! isset($decoded['items']) || ! is_array($decoded['items'])) {
            throw new RuntimeException('Respons OpenAI tidak valid.');
        }

        $items = [];
        foreach (array_slice($decoded['items'], 0, $count) as $item) {
            if (is_array($item)) {
                $items[] = GeneratedPostItem::fromArray($item);
            }
        }

        return $items;
    }
}
