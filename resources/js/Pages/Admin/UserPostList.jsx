import { Link, router } from '@inertiajs/react';
import Icon from '../../Components/Icon';
import Pagination from '../../Components/Pagination';
import ReadOnlyPostTable from '../../Components/ReadOnlyPostTable';
import SearchBar from '../../Components/SearchBar';
import AppLayout from '../../Layouts/AppLayout';

export default function UserPostList({ owner, posts, filters }) {
    const base = `/admin/users/${owner.id}/posts`;

    return (
        <AppLayout title={`Artikel ${owner.name}`}>
            <Link href="/admin/users" className="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-muted hover:text-ink">
                <Icon name="back" className="h-4 w-4" /> Kembali ke pengguna
            </Link>
            <div className="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 className="font-display text-3xl font-bold">Artikel {owner.name}</h1>
                    <p className="text-muted">@{owner.username} · tampilan read-only</p>
                </div>
                <a href={`${base}/export`} className="flex items-center gap-2 rounded-xl bg-ink px-4 py-2.5 text-sm font-semibold text-paper hover:bg-ink-2">
                    <Icon name="download" className="h-4 w-4" /> Ekspor ke Excel
                </a>
            </div>
            <div className="mt-6 flex flex-wrap gap-3">
                <SearchBar url={base} filters={filters} placeholder="Cari judul artikel..." />
                <select
                    aria-label="Filter status"
                    value={filters.status}
                    onChange={(e) => router.get(base, { ...filters, status: e.target.value }, { preserveState: true, replace: true })}
                    className="rounded-xl border border-line bg-white px-3.5 py-2.5"
                >
                    <option value="">Semua status</option>
                    <option value="published">Dipublikasikan</option>
                    <option value="draft">Draf</option>
                </select>
            </div>
            <div className="mt-5">
                <ReadOnlyPostTable posts={posts.data} />
            </div>
            <Pagination links={posts.links} />
        </AppLayout>
    );
}
