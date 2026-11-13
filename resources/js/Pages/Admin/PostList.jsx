import Icon from '../../Components/Icon';
import Pagination from '../../Components/Pagination';
import ReadOnlyPostTable from '../../Components/ReadOnlyPostTable';
import SearchBar from '../../Components/SearchBar';
import AppLayout from '../../Layouts/AppLayout';

export default function PostList({ posts, filters }) {
    return (
        <AppLayout title="Semua Artikel">
            <div className="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 className="font-display text-3xl font-bold">Semua Artikel</h1>
                    <p className="text-muted">Monitoring seluruh artikel sistem (read-only).</p>
                </div>
                <a href="/admin/posts/export" className="flex items-center gap-2 rounded-xl bg-ink px-4 py-2.5 text-sm font-semibold text-paper hover:bg-ink-2">
                    <Icon name="download" className="h-4 w-4" /> Ekspor semua ke Excel
                </a>
            </div>
            <div className="mt-6">
                <SearchBar url="/admin/posts" filters={filters} placeholder="Cari judul artikel..." />
            </div>
            <div className="mt-5">
                <ReadOnlyPostTable posts={posts.data} showAuthor />
            </div>
            <Pagination links={posts.links} />
        </AppLayout>
    );
}
