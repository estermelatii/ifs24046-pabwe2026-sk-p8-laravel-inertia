import { Head, Link, router, usePage } from '@inertiajs/react';
import Flash from '../Components/Flash';
import Icon from '../Components/Icon';
import Logo from '../Components/Logo';
import ProgressIndicator from '../Components/ProgressIndicator';

/**
 * Layout publik: Sticky Top Navbar (tanpa sidebar), konten membaca leluasa, footer editorial.
 */
export default function PublicLayout({ title, children }) {
    const { auth } = usePage().props;
    const user = auth.user;
    const dashboard = user?.role === 'admin' ? '/admin/users' : '/dashboard/posts';

    return (
        <div className="flex min-h-screen flex-col">
            <Head title={title} />
            <ProgressIndicator />
            <Flash />

            <header className="sticky top-0 z-40 bg-ink/95 text-paper backdrop-blur">
                <div className="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
                    <Logo light />
                    <nav className="flex items-center gap-2" aria-label="Navigasi utama">
                        <Link href="/articles" className="hidden rounded-lg px-3 py-2 text-sm font-medium hover:bg-white/10 sm:block">
                            Artikel
                        </Link>
                        {user ? (
                            <>
                                <Link href={dashboard} className="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium hover:bg-white/10">
                                    <span className="grid h-7 w-7 place-items-center rounded-full bg-paper text-xs font-bold text-ink">
                                        {user.name.charAt(0).toUpperCase()}
                                    </span>
                                    {user.role === 'admin' ? 'Panel Admin' : 'Dashboard Penulis'}
                                </Link>
                                <button
                                    type="button"
                                    onClick={() => router.post('/logout')}
                                    className="rounded-lg p-2 text-paper/70 hover:bg-white/10 hover:text-paper"
                                    aria-label="Keluar"
                                >
                                    <Icon name="logout" />
                                </button>
                            </>
                        ) : (
                            <>
                                <Link href="/login" className="flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold hover:bg-white/10">
                                    <Icon name="login" className="h-4 w-4" /> Masuk
                                </Link>
                                <Link href="/register" className="flex items-center gap-1.5 rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-white hover:bg-accent-2">
                                    <Icon name="user" className="h-4 w-4" /> Daftar
                                </Link>
                            </>
                        )}
                    </nav>
                </div>
            </header>

            <main className="flex-1">{children}</main>

            <footer className="mt-0 bg-ink text-paper">
                <div className="mx-auto grid max-w-6xl gap-8 px-4 py-12 sm:px-6 md:grid-cols-[1.5fr_1fr]">
                    <div>
                        <Logo light />
                        <p className="mt-3 max-w-md text-sm text-paper/70">
                            Platform publikasi artikel multi-tier. Baca tanpa login, menulis sebagai anggota, dan dipantau oleh admin.
                        </p>
                    </div>
                    <nav aria-label="Navigasi footer">
                        <p className="text-xs font-semibold uppercase tracking-widest text-paper/50">Navigasi</p>
                        <ul className="mt-3 space-y-2 text-sm">
                            <li><Link href="/" className="hover:text-sky-300">Beranda Publik</Link></li>
                            <li><Link href="/login" className="hover:text-sky-300">Halaman Login</Link></li>
                            <li><Link href="/register" className="hover:text-sky-300">Halaman Register</Link></li>
                        </ul>
                    </nav>
                </div>
                <p className="border-t border-white/10 py-4 text-center text-xs text-paper/50">© {new Date().getFullYear()} BlogVerse Pro</p>
            </footer>
        </div>
    );
}
