import { Link, useForm } from '@inertiajs/react';
import AuthShell from '../../Components/AuthShell';
import Field, { inputClass } from '../../Components/Field';
import Spinner from '../../Components/Spinner';

export default function Register() {
    const form = useForm({ name: '', username: '', email: '', password: '', password_confirmation: '' });
    const bind = (key) => ({
        value: form.data[key],
        onChange: (e) => form.setData(key, e.target.value),
        className: inputClass,
    });

    const submit = (event) => {
        event.preventDefault();
        form.post('/register');
    };

    return (
        <AuthShell title="Daftar" heading="Jadi penulis BlogVerse" subheading="Buat akun gratis dan mulai menerbitkan artikel.">
            <form onSubmit={submit} className="space-y-4">
                <Field label="Nama lengkap" id="register-name" error={form.errors.name}>
                    <input id="register-name" autoComplete="name" {...bind('name')} />
                </Field>
                <Field label="Username" id="register-username" error={form.errors.username}>
                    <input id="register-username" autoComplete="username" {...bind('username')} />
                </Field>
                <Field label="Email" id="register-email" error={form.errors.email}>
                    <input id="register-email" type="email" autoComplete="email" {...bind('email')} />
                </Field>
                <Field label="Password" id="register-password" error={form.errors.password}>
                    <input id="register-password" type="password" autoComplete="new-password" {...bind('password')} />
                </Field>
                <Field label="Konfirmasi password" id="register-password-confirmation">
                    <input id="register-password-confirmation" type="password" autoComplete="new-password" {...bind('password_confirmation')} />
                </Field>
                <button
                    id="register-submit"
                    type="submit"
                    disabled={form.processing}
                    className="flex w-full items-center justify-center gap-2 rounded-xl bg-accent px-4 py-3 font-semibold text-white hover:bg-accent-2 disabled:opacity-70"
                >
                    {form.processing && <Spinner />} Daftar
                </button>
            </form>
            <p className="mt-6 text-center text-sm text-muted">
                Sudah punya akun?{' '}
                <Link href="/login" className="font-semibold text-accent hover:underline">
                    Masuk
                </Link>
            </p>
        </AuthShell>
    );
}
