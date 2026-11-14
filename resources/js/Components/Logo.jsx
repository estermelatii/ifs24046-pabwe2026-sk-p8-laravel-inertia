import { Link } from '@inertiajs/react';

export default function Logo({ light = false, href = '/' }) {
    return (
        <Link href={href} className="flex items-center gap-2.5">
            <span className={`grid h-9 w-9 place-items-center rounded-xl font-display text-lg font-bold ${light ? 'bg-paper text-ink' : 'bg-ink text-paper'}`}>
                B
            </span>
            <span className={`font-display text-xl font-bold tracking-tight ${light ? 'text-paper' : 'text-ink'}`}>
                BlogVerse <span className={light ? 'text-sky-300' : 'text-accent'}>Pro</span>
            </span>
        </Link>
    );
}
