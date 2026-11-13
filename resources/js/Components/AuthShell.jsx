import { Head, Link } from '@inertiajs/react';
import Flash from './Flash';
import Icon from './Icon';
import Logo from './Logo';
import ProgressIndicator from './ProgressIndicator';

export default function AuthShell({ title, heading, subheading, children }) {
    return (
        <div className="grid min-h-screen lg:grid-cols-2">
            <Head title={title} />
            <ProgressIndicator />
            <Flash />

            <aside className="relative hidden flex-col justify-between overflow-hidden bg-ink p-12 text-paper lg:flex">
                <Logo light />
                <div>
                    <p className="font-display text-4xl font-bold leading-tight">
                        Tulis. Terbitkan. <span className="text-sky-300">Dibaca</span> semua orang.
                    </p>
                    <p className="mt-4 max-w-md text-paper/70">
                        Ruang menulis dengan editor Markdown, ekspor-impor Excel, dan asisten AI untuk draf artikel.
                    </p>
                </div>
                <div className="pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-full bg-accent/30 blur-3xl" />
                <div className="pointer-events-none absolute -bottom-24 left-10 h-72 w-72 rounded-full bg-sky-400/10 blur-3xl" />
            </aside>

            <main className="flex flex-col justify-center px-6 py-12 sm:px-12">
                <div className="mx-auto w-full max-w-md">
                    <Link href="/" className="mb-8 inline-flex items-center gap-1.5 text-sm font-medium text-muted hover:text-ink">
                        <Icon name="back" className="h-4 w-4" /> Kembali ke beranda
                    </Link>
                    <h1 className="font-display text-3xl font-bold">{heading}</h1>
                    <p className="mb-8 mt-2 text-muted">{subheading}</p>
                    {children}
                </div>
            </main>
        </div>
    );
}
