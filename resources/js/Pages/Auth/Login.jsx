import { Link, useForm } from '@inertiajs/react';
import AuthShell from '../../Components/AuthShell';
import Field, { inputClass } from '../../Components/Field';
import Spinner from '../../Components/Spinner';

export default function Login() {
    const form = useForm({ username: '', password: '', remember: false });

    const submit = (event) => {
        event.preventDefault();
        form.post('/login');
    };

    return (
        <AuthShell title="Masuk" heading="Selamat datang kembali" subheading="Masuk untuk menulis dan mengelola artikel Anda.">
            <form onSubmit={submit} className="space-y-5">
                <Field label="Username atau email" id="login-username" error={form.errors.username}>
                    <input
                        id="login-username"
                        name="username"
                        autoComplete="username"
                        value={form.data.username}
                        onChange={(e) => form.setData('username', e.target.value)}
                        className={inputClass}
                    />
                </Field>
                <Field label="Password" id="login-password" error={form.errors.password}>
                    <input
                        id="login-password"
                        name="password"
                        type="password"
                        autoComplete="current-password"
                        value={form.data.password}
                        onChange={(e) => form.setData('password', e.target.value)}
                        className={inputClass}
                    />
                </Field>
                <label className="flex items-center gap-2 text-sm">
                    <input type="checkbox" checked={form.data.remember} onChange={(e) => form.setData('remember', e.target.checked)} className="h-4 w-4 accent-accent" />
                    Ingat saya
                </label>
                <button
                    id="login-submit"
                    type="submit"
                    disabled={form.processing}
                    className="flex w-full items-center justify-center gap-2 rounded-xl bg-ink px-4 py-3 font-semibold text-paper hover:bg-ink-2 disabled:opacity-70"
                >
                    {form.processing && <Spinner />} Masuk
                </button>
            </form>
            <p className="mt-6 text-center text-sm text-muted">
                Belum punya akun?{' '}
                <Link href="/register" className="font-semibold text-accent hover:underline">
                    Daftar sebagai penulis
                </Link>
            </p>
        </AuthShell>
    );
}
