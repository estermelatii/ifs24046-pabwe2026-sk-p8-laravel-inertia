import { Link, router, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import AiGeneratorModal from '../../Components/AiGeneratorModal';
import ConfirmDialog from '../../Components/ConfirmDialog';
import Icon from '../../Components/Icon';
import KebabMenu from '../../Components/KebabMenu';
import Pagination from '../../Components/Pagination';
import PostFormModal from '../../Components/PostFormModal';
import { formatDate } from '../../Components/PostCard';
import PostStatusBadge from '../../Components/PostStatusBadge';
import AppLayout from '../../Layouts/AppLayout';

function StatCard({ label, value, tone }) {
    return (
        <div className={`rounded-2xl border border-line bg-white p-5 ${tone}`}>
            <p className="text-sm font-medium text-muted">{label}</p>
            <p className="mt-1 font-display text-4xl font-bold">{value}</p>
        </div>
    );
}

export default function Index({ posts, summary, filters, generated }) {
    const { errors } = usePage().props;
    const [search, setSearch] = useState(filters.search);
    const [formPost, setFormPost] = useState(undefined); // undefined=tutup, null=tambah, objek=ubah
    const [aiOpen, setAiOpen] = useState(false);
    const [deleting, setDeleting] = useState(null);
    const [busy, setBusy] = useState(false);
    const fileInput = useRef(null);

    useEffect(() => {
        if (generated) {
            setAiOpen(true);
        }
    }, [generated]);

    const visit = (params) =>
        router.get('/dashboard/posts', { ...filters, ...params }, { preserveState: true, preserveScroll: true, replace: true });

    const confirmDelete = () =>
        router.delete(`/dashboard/posts/${deleting.id}`, {
            preserveScroll: true,
            onStart: () => setBusy(true),
            onFinish: () => {
                setBusy(false);
                setDeleting(null);
            },
        });

    const importFile = (event) => {
        const file = event.target.files[0];

        if (file) {
            router.post('/dashboard/posts/import', { file }, { forceFormData: true, preserveScroll: true });
        }
        event.target.value = '';
    };

    return (
        <AppLayout title="Artikel Saya">
            <div className="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 className="font-display text-3xl font-bold">Artikel Saya</h1>
                    <p className="text-muted">Kelola tulisan, status publikasi, dan sampul artikel.</p>
                </div>
                <div className="flex flex-wrap gap-2">
                    <button type="button" id="ai-open" onClick={() => setAiOpen(true)} className="flex items-center gap-2 rounded-xl bg-ink px-4 py-2.5 text-sm font-semibold text-paper hover:bg-ink-2">
                        <Icon name="sparkles" className="h-4 w-4 text-sky-300" /> Rancang dengan AI
                    </button>
                    <a href="/dashboard/posts/export" className="flex items-center gap-2 rounded-xl border border-line bg-white px-4 py-2.5 text-sm font-semibold hover:border-ink">
                        <Icon name="download" className="h-4 w-4" /> Ekspor
                    </a>
                    <button type="button" onClick={() => fileInput.current.click()} className="flex items-center gap-2 rounded-xl border border-line bg-white px-4 py-2.5 text-sm font-semibold hover:border-ink">
                        <Icon name="upload" className="h-4 w-4" /> Impor
                    </button>
                    <input ref={fileInput} type="file" accept=".xlsx" onChange={importFile} className="hidden" aria-label="Impor file Excel" />
                    <button type="button" id="post-add" onClick={() => setFormPost(null)} className="flex items-center gap-2 rounded-xl bg-accent px-4 py-2.5 text-sm font-semibold text-white hover:bg-accent-2">
                        <Icon name="plus" className="h-4 w-4" /> Tambah Artikel
                    </button>
                </div>
            </div>

            {errors.file && <p role="alert" className="mt-4 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-danger">{errors.file}</p>}

            <div className="mt-6 grid gap-4 sm:grid-cols-3">
                <StatCard label="Total Artikel" value={summary.total} />
                <StatCard label="Total Selesai" value={summary.published} />
                <StatCard label="Belum Selesai" value={summary.draft} />
            </div>

            <div className="mt-8 flex flex-wrap items-center gap-3">
                <form onSubmit={(e) => { e.preventDefault(); visit({ search }); }} role="search" className="relative min-w-60 flex-1">
                    <Icon name="search" className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
                    <input
                        type="search"
                        aria-label="Cari artikel"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Cari judul artikel..."
                        className="w-full rounded-xl border border-line bg-white py-2.5 pl-9 pr-3 focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20"
                    />
                </form>
                <select aria-label="Filter status" value={filters.status} onChange={(e) => visit({ status: e.target.value })} className="rounded-xl border border-line bg-white px-3.5 py-2.5">
                    <option value="">Semua status</option>
                    <option value="published">Dipublikasikan</option>
                    <option value="draft">Draf</option>
                </select>
            </div>

            <ul className="mt-5 divide-y divide-line overflow-visible rounded-2xl border border-line bg-white">
                {posts.data.length === 0 && <li className="px-5 py-12 text-center text-muted">Belum ada artikel. Mulai menulis!</li>}
                {posts.data.map((post) => (
                    <li key={post.id} className="flex items-center gap-4 px-5 py-4">
                        <div className="min-w-0 flex-1">
                            <Link href={`/dashboard/posts/${post.id}`} className="block truncate font-semibold hover:text-accent">
                                {post.title}
                            </Link>
                            <p className="mt-0.5 text-sm text-muted">
                                {post.category} · {formatDate(post.created_at)}
                            </p>
                        </div>
                        <PostStatusBadge published={post.is_published} />
                        <button
                            type="button"
                            onClick={() => router.patch(`/dashboard/posts/${post.id}/publish`, {}, { preserveScroll: true })}
                            className="hidden items-center gap-1.5 rounded-lg border border-line px-3 py-1.5 text-xs font-semibold hover:border-ink sm:flex"
                        >
                            <Icon name={post.is_published ? 'draft' : 'send'} className="h-3.5 w-3.5" />
                            {post.is_published ? 'Jadikan Draf' : 'Publikasikan'}
                        </button>
                        <KebabMenu
                            label={post.title}
                            onDetail={() => router.visit(`/dashboard/posts/${post.id}`)}
                            onEdit={() => router.visit(`/dashboard/posts/${post.id}`, { data: { edit: 1 } })}
                            onDelete={() => setDeleting(post)}
                        />
                    </li>
                ))}
            </ul>
            <Pagination links={posts.links} />

            {formPost !== undefined && <PostFormModal post={formPost} onClose={() => setFormPost(undefined)} />}
            {aiOpen && <AiGeneratorModal generated={generated} onClose={() => setAiOpen(false)} />}
            <ConfirmDialog
                open={deleting !== null}
                title="Hapus artikel?"
                message={deleting ? `Artikel "${deleting.title}" akan dihapus permanen.` : ''}
                processing={busy}
                onCancel={() => setDeleting(null)}
                onConfirm={confirmDelete}
            />
        </AppLayout>
    );
}
