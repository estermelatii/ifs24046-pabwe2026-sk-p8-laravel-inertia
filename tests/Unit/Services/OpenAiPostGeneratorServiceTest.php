<?php

namespace Tests\Unit\Services;

use App\Features\Ai\DTO\GeneratedPostItem;
use App\Features\Ai\Services\OpenAiPostGeneratorService;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class OpenAiPostGeneratorServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['openai.api_key' => 'test-key', 'openai.base_url' => 'https://api.test/v1']);
    }

    private function fakeContent(string $content, int $status = 200): void
    {
        Http::fake(['api.test/*' => Http::response(['choices' => [['message' => ['content' => $content]]]], $status)]);
    }

    public function test_parses_items_and_sends_auth_and_model(): void
    {
        $this->fakeContent(json_encode(['items' => [
            ['title' => 'Satu', 'category' => 'Teknologi', 'excerpt' => 'e', 'content' => 'c'],
            'bukan-array',
            ['title' => 'Dua'],
        ]]));

        $items = (new OpenAiPostGeneratorService)->generate('topik', 3);

        $this->assertCount(2, $items);
        $this->assertContainsOnlyInstancesOf(GeneratedPostItem::class, $items);
        $this->assertSame('Satu', $items[0]->title);
        Http::assertSent(fn ($r) => $r->hasHeader('Authorization', 'Bearer test-key')
            && $r['model'] === config('openai.model')
            && str_contains($r['messages'][0]['content'], 'Teknologi'));
    }

    public function test_count_is_clamped(): void
    {
        $this->fakeContent(json_encode(['items' => [['title' => 'A'], ['title' => 'B']]]));

        $this->assertCount(1, (new OpenAiPostGeneratorService)->generate('x', 0));
    }

    public function test_missing_api_key_throws(): void
    {
        config(['openai.api_key' => null]);
        $this->expectException(RuntimeException::class);

        (new OpenAiPostGeneratorService)->generate('x');
    }

    public function test_http_failure_throws(): void
    {
        $this->fakeContent('{}', 500);
        $this->expectException(RuntimeException::class);

        (new OpenAiPostGeneratorService)->generate('x');
    }

    public function test_invalid_json_throws(): void
    {
        $this->fakeContent('bukan json');
        $this->expectException(RuntimeException::class);

        (new OpenAiPostGeneratorService)->generate('x');
    }
}
