import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import ConfirmDialog from '../../Components/ConfirmDialog';
import Icon from '../../Components/Icon';
import MarkdownViewer from '../../Components/MarkdownViewer';
import PostFormModal from '../../Components/PostFormModal';
import { formatDate } from '../../Components/PostCard';
import PostStatusBadge from '../../Components/PostStatusBadge';
import Spinner from '../../Components/Spinner';
import AppLayout from '../../Layouts/AppLayout';

export default function Show({ post, openEdit }) {
    const [editing, setEditing] = useState(openEdit);
    const [deleting, setDeleting] = useState(false);
    const [busy, setBusy] = useState(false);
    const [uploading, setUploading] = useState(false);

    const upload = (event) => {
        const file = event.target.files[0];

        if (file) {
            router.post(`/dashboard/posts/${post.id}/cover`, { cover: file }, {
                forceFormData: true,
                preserveScroll: true,
                onStart: () => setUploading(true),
                onFinish: () => setUploading(false),
            });
        }
        event.target.value = '';
    };

    const remove = () =>
        router.delete(`/dashboard/posts/${post.id}`, {
            onStart: () => setBusy(true),
            onFinish: () => setBusy(false),
        });

    return (
        <AppLayout title={post.title}>
            <Link href="/dashboard/posts" className="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-muted hover:text-ink">
                <Icon name="back" className="h-4 w-4" /> Kembali ke daftar
            </Link>

            <div className="rounded-2xl border border-line bg-white p-6 sm:p-10">
                <div className="flex flex-wrap items-center gap-3">
                    <PostStatusBadge published={post.is_published} />
                    <span className="text-xs font-semibold uppercase tracking-widest text-accent">{post.category}</span>
                    <span className="text-sm text-muted">{formatDate(post.created_at)}</span>
                </div>
                <h1 className="mt-3 font-display text-3xl font-bold sm:text-4xl">{post.title}</h1>
                {post.excerpt && <p className="mt-3 text-lg text-muted">{post.excerpt}</p>}

                <div className="mt-6 flex flex-wrap gap-2">
                    <button type="button" id="post-edit" onClick={() => setEditing(true)} className="flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white hover:bg-ink-2">
                        <Icon name="pencil" className="h-4 w-4" /> Ubah
                    </button>
                    <button type="button" onClick={() => router.patch(`/dashboard/posts/${post.id}/publish`, {}, { preserveScroll: true })} className="flex items-center gap-2 rounded-xl border border-line px-4 py-2.5 text-sm font-semibold hover:border-ink">
                        <Icon name={post.is_published ? 'draft' : 'send'} className="h-4 w-4" />
                        {post.is_published ? 'Jadikan Draf' : 'Publikasikan'}
                    </button>
                    {post.is_published && (
                        <Link href={`/articles/${post.slug}`} className="flex items-center gap-2 rounded-xl border border-line px-4 py-2.5 text-sm font-semibold hover:border-ink">
                            <Icon name="eye" className="h-4 w-4" /> Lihat di blog
                        </Link>
                    )}
                    <button type="button" onClick={() => setDeleting(true)} className="flex items-center gap-2 rounded-xl border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">
                        <Icon name="trash" className="h-4 w-4" /> Hapus
                    </button>
                </div>

                <section className="mt-8" aria-label="Foto sampul">
                    {post.cover_url ? (
                        <img src={post.cover_url} alt={`Sampul ${post.title}`} className="max-h-96 w-full rounded-xl object-cover" />
                    ) : (
                        <div className="grid h-40 place-items-center rounded-xl border border-dashed border-line bg-paper text-muted">
                            <Icon name="image" className="h-8 w-8" />
                        </div>
                    )}
                    <div className="mt-3 flex gap-2">
                        <label className="flex items-center gap-2 rounded-lg border border-line px-3 py-1.5 text-sm font-semibold hover:border-ink">
                            {uploading ? <Spinner /> : <Icon name="upload" className="h-4 w-4" />} Unggah sampul
                            <input type="file" accept="image/*" onChange={upload} className="sr-only" />
                        </label>
                        {post.cover_url && (
                            <button type="button" onClick={() => router.delete(`/dashboard/posts/${post.id}/cover`, { preserveScroll: true })} className="rounded-lg px-3 py-1.5 text-sm font-semibold text-red-600 hover:bg-red-50">
                                Hapus sampul
                            </button>
                        )}
                    </div>
                </section>

                <hr className="my-8 border-line" />
                <MarkdownViewer content={post.content} />
            </div>

            {editing && <PostFormModal post={post} onClose={() => setEditing(false)} />}
            <ConfirmDialog
                open={deleting}
                title="Hapus artikel?"
                message={`Artikel "${post.title}" akan dihapus permanen.`}
                processing={busy}
                onCancel={() => setDeleting(false)}
                onConfirm={remove}
            />
        </AppLayout>
    );
}
