import { router } from '@inertiajs/react';
import { useState } from 'react';
import Icon from './Icon';

export default function SearchBar({ url, filters, placeholder }) {
    const [search, setSearch] = useState(filters.search);

    return (
        <form
            role="search"
            onSubmit={(e) => {
                e.preventDefault();
                router.get(url, { ...filters, search }, { preserveState: true, replace: true });
            }}
            className="relative w-full md:w-96"
        >
            <Icon name="search" className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
            <input
                type="search"
                aria-label={placeholder}
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                placeholder={placeholder}
                className="w-full rounded-xl border border-line bg-white py-2.5 pl-9 pr-3 focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20"
            />
        </form>
    );
}
