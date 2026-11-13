import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import Flash from '../Components/Flash';
import Icon from '../Components/Icon';
import Logo from '../Components/Logo';
import ProgressIndicator from '../Components/ProgressIndicator';

const memberMenu = [
    { href: '/dashboard/posts', label: 'Artikel Saya', icon: 'file' },
    { href: '/profile', label: 'Profil', icon: 'user' },
];
const adminMenu = [
    { href: '/admin/users', label: 'Pengguna', icon: 'users' },
    { href: '/admin/posts', label: 'Semua Artikel', icon: 'layers' },
    { href: '/profile', label: 'Profil', icon: 'user' },
];

/**
 * Layout dashboard (anggota & admin) dengan sidebar.
 */
export default function AppLayout({ title, children }) {
    const { props, url } = usePage();
    const { auth } = props;
    const [open, setOpen] = useState(false);
    const user = auth.user;
    const menu = user.role === 'admin' ? adminMenu : memberMenu;

    const nav = (
        <nav className="space-y-1" aria-label="Menu dashboard">
            {menu.map((item) => {
                const active = url === item.href || url.startsWith(`${item.href}/`) || url.startsWith(`${item.href}?`);

                return (
                    <Link
                        key={item.href}
                        href={item.href}
                        onClick={() => setOpen(false)}
                        className={`flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium ${active ? 'bg-paper text-ink' : 'text-paper/75 hover:bg-white/10 hover:text-paper'}`}
                    >
                        <Icon name={item.icon} className="h-[18px] w-[18px]" /> {item.label}
                    </Link>
                );
            })}
            <Link href="/" className="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium text-paper/75 hover:bg-white/10 hover:text-paper">
                <Icon name="arrow" className="h-[18px] w-[18px]" /> Lihat Blog
            </Link>
        </nav>
    );

    return (
        <div className="min-h-screen bg-paper-2 lg:flex">
            <Head title={title} />
            <ProgressIndicator />
            <Flash />

            <aside className="hidden w-64 shrink-0 flex-col bg-ink p-5 lg:flex">
                <Logo light href="/" />
                <p className="mb-2 mt-8 px-3.5 text-[11px] font-semibold uppercase tracking-widest text-paper/40">
                    {user.role === 'admin' ? 'Panel Admin' : 'Dashboard Penulis'}
                </p>
                {nav}
                <div className="mt-auto border-t border-white/10 pt-4">
                    <p className="truncate px-1 text-sm font-semibold text-paper">{user.name}</p>
                    <p className="truncate px-1 text-xs text-paper/50">@{user.username}</p>
                    <button
                        type="button"
                        onClick={() => router.post('/logout')}
                        className="mt-3 flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium text-paper/75 hover:bg-white/10 hover:text-paper"
                    >
                        <Icon name="logout" className="h-4 w-4" /> Keluar
                    </button>
                </div>
            </aside>

            <div className="flex min-w-0 flex-1 flex-col">
                <header className="flex h-14 items-center justify-between bg-ink px-4 text-paper lg:hidden">
                    <Logo light href="/" />
                    <button type="button" onClick={() => setOpen(!open)} aria-label="Buka menu" aria-expanded={open} className="rounded-lg p-2 hover:bg-white/10">
                        <Icon name={open ? 'x' : 'dots'} />
                    </button>
                </header>
                {open && <div className="bg-ink px-4 pb-4 lg:hidden">{nav}</div>}

                <main className="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:px-8">{children}</main>
            </div>
        </div>
    );
}
