import { Link } from '@inertiajs/react';

export default function Pagination({ links }) {
    if (!links || links.length <= 3) {
        return null;
    }

    return (
        <nav className="mt-6 flex flex-wrap items-center justify-center gap-1.5" aria-label="Halaman">
            {links.map((link, index) => {
                const label = link.label
                    .replace('&laquo; Previous', '‹ Sebelumnya')
                    .replace('Next &raquo;', 'Berikutnya ›');
                const base = 'rounded-lg px-3 py-1.5 text-sm font-medium';

                if (!link.url) {
                    return (
                        <span key={index} className={`${base} text-muted/50`}>
                            {label}
                        </span>
                    );
                }

                return (
                    <Link
                        key={index}
                        href={link.url}
                        preserveScroll
                        className={`${base} ${link.active ? 'bg-ink text-paper' : 'text-ink hover:bg-paper-2'}`}
                    >
                        {label}
                    </Link>
                );
            })}
        </nav>
    );
}
