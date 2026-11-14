import { Link } from '@inertiajs/react';

export function formatDate(iso) {
    return iso ? new Date(iso).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '';
}

export default function PostCard({ post, featured = false, as: Heading = 'h3' }) {
    return (
        <article className={`group flex flex-col overflow-hidden rounded-2xl border border-line bg-white transition hover:-translate-y-0.5 hover:shadow-lg ${featured ? 'md:col-span-2 md:flex-row' : ''}`}>
            <Link href={`/articles/${post.slug}`} className={`block overflow-hidden bg-paper-2 ${featured ? 'md:w-1/2' : ''}`} tabIndex={-1} aria-hidden="true">
                {post.cover_url ? (
                    <img src={post.cover_url} alt="" loading="lazy" className={`w-full object-cover transition duration-500 group-hover:scale-105 ${featured ? 'h-64 md:h-full' : 'h-48'}`} />
                ) : (
                    <div className={`grid w-full place-items-center bg-gradient-to-br from-accent-soft to-paper-2 font-display text-6xl font-bold text-accent/40 ${featured ? 'h-64 md:h-full' : 'h-48'}`}>
                        {post.title.charAt(0)}
                    </div>
                )}
            </Link>
            <div className="flex flex-1 flex-col p-5">
                <span className="text-xs font-semibold uppercase tracking-widest text-accent">{post.category}</span>
                <Heading className={`mt-2 font-display font-bold leading-snug ${featured ? 'text-2xl' : 'text-xl'}`}>
                    <Link href={`/articles/${post.slug}`} className="hover:text-accent">
                        {post.title}
                    </Link>
                </Heading>
                {post.excerpt && <p className="mt-2 line-clamp-3 text-muted">{post.excerpt}</p>}
                <p className="mt-auto pt-4 text-sm text-muted">
                    {post.author} · {formatDate(post.published_at)}
                </p>
            </div>
        </article>
    );
}
