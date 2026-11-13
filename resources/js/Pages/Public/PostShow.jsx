import { Link } from '@inertiajs/react';
import Icon from '../../Components/Icon';
import MarkdownViewer from '../../Components/MarkdownViewer';
import { formatDate } from '../../Components/PostCard';
import PublicLayout from '../../Layouts/PublicLayout';

export default function PostShow({ post }) {
    return (
        <PublicLayout title={post.title}>
            <article className="mx-auto max-w-3xl px-4 py-12 sm:px-6">
                <Link href="/articles" className="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-muted hover:text-ink">
                    <Icon name="back" className="h-4 w-4" /> Semua artikel
                </Link>
                <p className="text-xs font-semibold uppercase tracking-widest text-accent">{post.category_description}</p>
                <h1 className="mt-3 font-display text-4xl font-bold leading-tight sm:text-5xl">{post.title}</h1>
                {post.excerpt && <p className="mt-4 text-xl text-muted">{post.excerpt}</p>}
                <p className="mt-5 border-y border-line py-3 text-sm text-muted">
                    Oleh <strong className="text-ink">{post.author}</strong> · {formatDate(post.published_at)}
                </p>
                {post.cover_url && <img src={post.cover_url} alt={`Sampul ${post.title}`} className="mt-8 w-full rounded-2xl object-cover" />}
                <div className="mt-8">
                    <MarkdownViewer content={post.content} />
                </div>

                <aside className="mt-14 rounded-2xl bg-ink p-8 text-paper">
                    <h2 className="font-display text-2xl font-bold">Suka tulisan seperti ini?</h2>
                    <p className="mt-2 text-paper/70">Bergabunglah dengan komunitas BlogVerse dan terbitkan artikelmu sendiri.</p>
                    <div className="mt-5 flex flex-wrap gap-3">
                        <Link href="/login" className="flex items-center gap-2 rounded-xl bg-paper px-5 py-2.5 text-sm font-semibold text-ink hover:bg-sky-100">
                            <Icon name="login" className="h-4 w-4" /> Masuk
                        </Link>
                        <Link href="/register" className="flex items-center gap-2 rounded-xl border border-paper/40 px-5 py-2.5 text-sm font-semibold hover:bg-white/10">
                            <Icon name="user" className="h-4 w-4" /> Daftar
                        </Link>
                    </div>
                </aside>
            </article>
        </PublicLayout>
    );
}
