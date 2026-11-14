import { Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import Icon from '../../Components/Icon';
import Pagination from '../../Components/Pagination';
import PostCard from '../../Components/PostCard';
import Skyline from '../../Components/Skyline';
import PublicLayout from '../../Layouts/PublicLayout';

const tiers = [
    { icon: 'eye', title: 'Baca Tanpa Login', text: 'Jelajahi artikel, cari berdasarkan judul atau isi, dan saring per kategori kapan saja.', cta: 'Jelajahi Artikel', href: '/articles' },
    { icon: 'pencil', title: 'Tulis & Terbitkan', text: 'Editor Markdown, foto sampul, impor-ekspor Excel, dan asisten AI untuk draf artikelmu.', cta: 'Daftar Penulis', href: '/register' },
    { icon: 'layers', title: 'Dipantau Admin', text: 'Admin memantau seluruh artikel dan pengguna, lengkap dengan ekspor data ke Excel.', cta: 'Masuk Panel', href: '/login' },
];

const strengths = [
    { icon: 'file', title: 'Markdown Native', text: 'Tulis dengan TOAST UI Editor dan tampil rapi di halaman baca.' },
    { icon: 'sparkles', title: 'Draf dengan AI', text: 'Hasilkan beberapa draf artikel sekaligus, simpan sebagai draf.' },
    { icon: 'download', title: 'Excel Dua Arah', text: 'Ekspor tulisanmu dan impor ratusan artikel dari satu berkas.' },
    { icon: 'check', title: 'Draf & Publikasi', text: 'Atur status tiap artikel dengan satu klik.' },
];

export default function PostIndex({ posts, filters, isHome }) {
    const { categories, auth } = usePage().props;
    const [search, setSearch] = useState(filters.search);

    const visit = (params) =>
        router.get('/articles', { ...filters, ...params }, { preserveState: true, preserveScroll: true, replace: true });

    const submit = (event) => {
        event.preventDefault();
        visit({ search });
    };

    const showHero = isHome && !filters.search && !filters.category;
    const dark = showHero;

    return (
        <PublicLayout title={isHome ? 'Beranda' : 'Artikel'}>
            {showHero && (
                <>
                    <section className="hero-skyline relative overflow-hidden text-paper">
                        <Skyline className="pointer-events-none absolute inset-x-0 bottom-0 h-64 w-full opacity-70" />
                        <div className="relative mx-auto max-w-6xl px-4 pb-44 pt-16 sm:px-6 md:pb-52 md:pt-24">
                            <p className="text-sm font-semibold uppercase tracking-widest text-sky-300">Platform blog multi-tier</p>
                            <h1 className="mt-4 max-w-3xl font-display text-4xl font-medium leading-tight sm:text-6xl">
                                Menerbitkan Ide, Membangun Komunitas Penulis.
                            </h1>
                            <p className="mt-5 max-w-xl text-lg text-paper/80">
                                Baca artikel pilihan tanpa akun, atau bergabung sebagai penulis dan terbitkan tulisanmu sendiri.
                            </p>
                            {!auth.user && (
                                <div className="mt-8 flex flex-wrap gap-3">
                                    <Link href="/login" className="flex items-center gap-2 rounded-lg bg-accent px-6 py-3 font-semibold text-white hover:bg-accent-2">
                                        <Icon name="login" className="h-4 w-4" /> Masuk
                                    </Link>
                                    <Link href="/register" className="flex items-center gap-2 rounded-lg border border-white/40 px-6 py-3 font-semibold hover:bg-white/10">
                                        <Icon name="user" className="h-4 w-4" /> Daftar Penulis
                                    </Link>
                                </div>
                            )}
                        </div>
                    </section>

                    <section className="relative z-10 -mt-32 bg-ink pb-14 md:-mt-40" aria-label="Tiga tingkatan akses">
                        <div className="mx-auto grid max-w-6xl gap-5 px-4 sm:px-6 md:grid-cols-3">
                            {tiers.map((t) => (
                                <div key={t.title} className="flex flex-col rounded-xl border border-white/15 bg-ink-2/80 p-6 text-paper shadow-xl backdrop-blur">
                                    <span className="grid h-11 w-11 place-items-center rounded-lg bg-white/10 text-sky-300">
                                        <Icon name={t.icon} />
                                    </span>
                                    <h2 className="mt-4 font-display text-xl font-medium">{t.title}</h2>
                                    <p className="mt-2 flex-1 text-sm text-paper/70">{t.text}</p>
                                    <Link href={t.href} className="mt-5 inline-flex w-fit items-center gap-2 rounded-md bg-paper px-4 py-2 text-sm font-semibold text-ink hover:bg-sky-100">
                                        {t.cta} <Icon name="arrow" className="h-4 w-4" />
                                    </Link>
                                </div>
                            ))}
                        </div>
                    </section>

                    <section className="bg-white">
                        <div className="mx-auto grid max-w-6xl gap-10 px-4 py-16 sm:px-6 md:grid-cols-2">
                            <div>
                                <h2 className="font-display text-3xl font-medium">Tentang BlogVerse</h2>
                                <p className="mt-4 text-muted">
                                    BlogVerse Pro adalah platform publikasi artikel dengan tiga tingkatan akses: pembaca umum, penulis anggota, dan admin.
                                    Semua dirancang agar menulis terasa ringan dan membaca terasa nyaman.
                                </p>
                                <Link href="/articles" className="mt-6 inline-flex items-center gap-2 rounded-lg bg-accent px-5 py-2.5 text-sm font-semibold text-white hover:bg-accent-2">
                                    Baca Artikel <Icon name="arrow" className="h-4 w-4" />
                                </Link>
                            </div>
                            <div>
                                <h2 className="font-display text-3xl font-medium">Keunggulan Kami</h2>
                                <div className="mt-6 grid gap-6 sm:grid-cols-2">
                                    {strengths.map((s) => (
                                        <div key={s.title} className="flex gap-3">
                                            <span className="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-accent-soft text-accent">
                                                <Icon name={s.icon} className="h-5 w-5" />
                                            </span>
                                            <div>
                                                <h3 className="font-semibold">{s.title}</h3>
                                                <p className="text-sm text-muted">{s.text}</p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    </section>
                </>
            )}

            <section className={dark ? 'bg-ink text-paper' : ''}>
                <div className="mx-auto max-w-6xl px-4 py-12 sm:px-6">
                    <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        {showHero ? <h2 className="font-display text-3xl font-medium">Artikel Pilihan</h2> : <h1 className="font-display text-3xl font-medium">Semua Artikel</h1>}
                        <form onSubmit={submit} role="search" className="relative w-full md:w-80">
                            <Icon name="search" className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
                            <input
                                type="search"
                                aria-label="Cari artikel"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Cari judul atau isi artikel..."
                                className="w-full rounded-lg border border-line bg-white py-2.5 pl-9 pr-3 text-ink focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/30"
                            />
                        </form>
                    </div>

                    <div className="mt-5 flex flex-wrap gap-2" role="group" aria-label="Filter kategori">
                        {[{ value: '', label: 'Semua' }, ...categories].map((c) => {
                            const active = filters.category === c.value;
                            const tone = active
                                ? 'border-accent bg-accent text-white'
                                : dark
                                  ? 'border-white/30 text-paper hover:border-white'
                                  : 'border-line bg-white hover:border-ink';

                            return (
                                <button key={c.value} type="button" onClick={() => visit({ category: c.value })} className={`rounded-full border px-4 py-1.5 text-sm font-medium ${tone}`}>
                                    {c.value || 'Semua'}
                                </button>
                            );
                        })}
                    </div>

                    {posts.data.length === 0 ? (
                        <p className={`mt-16 text-center ${dark ? 'text-paper/70' : 'text-muted'}`}>Belum ada artikel yang cocok.</p>
                    ) : (
                        <div className="mt-8 grid gap-6 text-ink md:grid-cols-2 lg:grid-cols-3">
                            {posts.data.map((post, index) => (
                                <PostCard key={post.id} post={post} featured={showHero && index === 0 && posts.current_page === 1} />
                            ))}
                        </div>
                    )}
                    <div className={dark ? '[&_a]:text-paper [&_a:hover]:bg-white/10 [&_a.bg-ink]:bg-accent' : ''}>
                        <Pagination links={posts.links} />
                    </div>
                </div>
            </section>
        </PublicLayout>
    );
}
