import { Link } from '@inertiajs/react';
import { formatDate } from './PostCard';
import PostStatusBadge from './PostStatusBadge';

export default function ReadOnlyPostTable({ posts, showAuthor = false }) {
    return (
        <ul className="divide-y divide-line rounded-2xl border border-line bg-white">
            {posts.length === 0 && <li className="px-5 py-12 text-center text-muted">Belum ada artikel.</li>}
            {posts.map((post) => (
                <li key={post.id} className="flex items-center gap-4 px-5 py-4">
                    <div className="min-w-0 flex-1">
                        <p className="truncate font-semibold">{post.title}</p>
                        <p className="mt-0.5 text-sm text-muted">
                            {showAuthor && <>@{post.author_username} · </>}
                            {post.category} · {formatDate(post.created_at)}
                        </p>
                    </div>
                    <PostStatusBadge published={post.is_published} />
                    {post.is_published && (
                        <Link href={`/articles/${post.slug}`} className="text-sm font-semibold text-primary hover:underline">
                            Buka
                        </Link>
                    )}
                </li>
            ))}
        </ul>
    );
}
