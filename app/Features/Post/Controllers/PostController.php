<?php

namespace App\Features\Post\Controllers;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Features\Post\Services\PostExcelService;
use App\Features\Post\Services\PostService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Throwable;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class PostController extends Controller
{
    public function __construct(private readonly PostService $posts) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();

        return Inertia::render('Post/Index', [
            'posts' => $this->posts->listForUser($user, $search, $status)
                ->through(fn (Post $post) => $post->toListItem()),
            'summary' => $this->posts->summary($user),
            'filters' => ['search' => $search, 'status' => $status],
            'generated' => $request->session()->get('generated'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Post::class);

        $this->posts->create($request->user(), $this->validated($request));

        return redirect()->route('posts.index')->with('success', 'Artikel berhasil dibuat.');
    }

    public function show(Request $request, Post $post): Response
    {
        return Inertia::render('Post/Show', [
            'post' => $post->toDetail(),
            'openEdit' => $request->boolean('edit'),
        ]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        Gate::authorize('update', $post);

        $this->posts->update($post, $this->validated($request));

        return back()->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        Gate::authorize('delete', $post);

        $this->posts->delete($post);

        return redirect()->route('posts.index')->with('success', 'Artikel berhasil dihapus.');
    }

    public function togglePublish(Post $post): RedirectResponse
    {
        Gate::authorize('update', $post);

        $post = $this->posts->togglePublish($post);

        return back()->with('success', $post->is_published ? 'Artikel dipublikasikan.' : 'Artikel dijadikan draf.');
    }

    public function uploadCover(Request $request, Post $post): RedirectResponse
    {
        Gate::authorize('update', $post);

        $data = $request->validate([
            'cover' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $this->posts->storeCover($post, $data['cover']);

        return back()->with('success', 'Foto sampul berhasil diunggah.');
    }

    public function removeCover(Post $post): RedirectResponse
    {
        Gate::authorize('update', $post);

        $this->posts->removeCover($post);

        return back()->with('success', 'Foto sampul dihapus.');
    }

    public function export(Request $request, PostExcelService $excel, PostRepositoryInterface $repository): HttpResponse
    {
        return $this->download($excel->export($repository->allForUser($request->user())), 'artikel-saya.xlsx');
    }

    public function import(Request $request, PostExcelService $excel): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx', 'max:5120'],
        ]);

        try {
            $count = $excel->import($request->user(), $request->file('file')->getRealPath());
        } catch (Throwable) {
            return back()->withErrors(['file' => 'File Excel tidak dapat dibaca.']);
        }

        return back()->with('success', "{$count} artikel berhasil diimpor.");
    }

    public function aiGenerate(Request $request, AiClientInterface $ai): RedirectResponse
    {
        $data = $request->validate([
            'prompt' => ['required', 'string', 'max:500'],
            'count' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        try {
            $items = $ai->generate($data['prompt'], (int) $data['count']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['prompt' => $e->getMessage()]);
        }

        return back()->with('generated', array_map(fn ($item) => $item->toArray(), $items));
    }

    public function aiSave(Request $request): RedirectResponse
    {
        Gate::authorize('create', Post::class);

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:5'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.category' => ['nullable', 'string'],
            'items.*.excerpt' => ['nullable', 'string', 'max:500'],
            'items.*.content' => ['required', 'string'],
        ]);

        $count = $this->posts->createFromGenerated($request->user(), $data['items']);

        return redirect()->route('posts.index')->with('success', "{$count} draf artikel dari AI berhasil disimpan.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(PostCategory::values())],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'is_published' => ['sometimes', 'boolean'],
        ]);
    }
}
