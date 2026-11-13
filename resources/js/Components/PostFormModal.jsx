import { useForm } from '@inertiajs/react';
import Field, { inputClass } from './Field';
import Icon from './Icon';
import MarkdownEditor from './MarkdownEditor';
import Spinner from './Spinner';
import { usePage } from '@inertiajs/react';

/**
 * Modal Tambah/Ubah artikel — Full Screen, header & footer berwarna kontras,
 * tombol Batal & Simpan di sisi kanan footer.
 */
export default function PostFormModal({ post, onClose }) {
    const { categories } = usePage().props;
    const editing = Boolean(post);
    const form = useForm({
        title: post?.title ?? '',
        category: post?.category ?? categories[0].value,
        excerpt: post?.excerpt ?? '',
        content: post?.content ?? '',
        is_published: post?.is_published ?? false,
    });

    const submit = (event) => {
        event.preventDefault();
        const options = { preserveScroll: true, onSuccess: onClose };

        if (editing) {
            form.put(`/dashboard/posts/${post.id}`, options);
        } else {
            form.post('/dashboard/posts', options);
        }
    };

    return (
        <div className="fixed inset-0 z-50 flex flex-col bg-paper" role="dialog" aria-modal="true" aria-labelledby="post-modal-title">
            <header className={`flex items-center justify-between px-5 py-4 text-white sm:px-8 ${editing ? 'bg-primary' : 'bg-accent'}`}>
                <h2 id="post-modal-title" className="font-display text-xl font-bold">
                    {editing ? 'Ubah Artikel' : 'Tambah Artikel Baru'}
                </h2>
                <button type="button" onClick={onClose} aria-label="Tutup" className="grid h-9 w-9 place-items-center rounded-lg hover:bg-white/15">
                    <Icon name="x" />
                </button>
            </header>

            <form onSubmit={submit} className="flex min-h-0 flex-1 flex-col">
                <div className="flex-1 space-y-5 overflow-y-auto px-5 py-6 sm:px-8">
                    <div className="mx-auto max-w-4xl space-y-5">
                        <Field label="Judul" id="post-title" error={form.errors.title}>
                            <input id="post-title" value={form.data.title} onChange={(e) => form.setData('title', e.target.value)} className={inputClass} placeholder="Judul artikel" />
                        </Field>
                        <div className="grid gap-5 sm:grid-cols-2">
                            <Field label="Kategori" id="post-category" error={form.errors.category}>
                                <select id="post-category" value={form.data.category} onChange={(e) => form.setData('category', e.target.value)} className={inputClass}>
                                    {categories.map((c) => (
                                        <option key={c.value} value={c.value}>
                                            {c.value} — {c.label}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <Field label="Status" id="post-published">
                                <label className="flex h-[46px] items-center gap-3 rounded-xl border border-line bg-white px-3.5">
                                    <input
                                        id="post-published"
                                        type="checkbox"
                                        checked={form.data.is_published}
                                        onChange={(e) => form.setData('is_published', e.target.checked)}
                                        className="h-4 w-4 accent-accent"
                                    />
                                    <span className="text-sm font-medium">Publikasikan ke umum</span>
                                </label>
                            </Field>
                        </div>
                        <Field label="Ringkasan" id="post-excerpt" error={form.errors.excerpt} hint="1–2 kalimat pemikat pembaca.">
                            <textarea id="post-excerpt" rows={2} value={form.data.excerpt} onChange={(e) => form.setData('excerpt', e.target.value)} className={inputClass} />
                        </Field>
                        <Field label="Isi Artikel (Markdown)" id="markdown-editor" error={form.errors.content}>
                            <MarkdownEditor value={form.data.content} onChange={(value) => form.setData('content', value)} />
                        </Field>
                    </div>
                </div>

                <footer className={`flex justify-end gap-3 px-5 py-4 sm:px-8 ${editing ? 'bg-primary/10' : 'bg-accent-soft'}`}>
                    <button type="button" onClick={onClose} className="rounded-xl px-5 py-2.5 text-sm font-semibold hover:bg-black/5">
                        Batal
                    </button>
                    <button
                        type="submit"
                        id="post-save"
                        disabled={form.processing}
                        className={`flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white disabled:opacity-70 ${editing ? 'bg-primary hover:bg-ink-2' : 'bg-accent hover:bg-accent-2'}`}
                    >
                        {form.processing && <Spinner />} Simpan
                    </button>
                </footer>
            </form>
        </div>
    );
}
