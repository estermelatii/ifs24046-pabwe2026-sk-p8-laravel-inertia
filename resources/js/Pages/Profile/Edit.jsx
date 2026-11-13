import { useForm, usePage } from '@inertiajs/react';
import Field, { inputClass } from '../../Components/Field';
import Spinner from '../../Components/Spinner';
import AppLayout from '../../Layouts/AppLayout';

function Card({ title, children }) {
    return (
        <section className="rounded-2xl border border-line bg-white p-6">
            <h2 className="mb-5 font-display text-xl font-bold">{title}</h2>
            {children}
        </section>
    );
}

function Submit({ processing, children }) {
    return (
        <button type="submit" disabled={processing} className="flex items-center gap-2 rounded-xl bg-ink px-5 py-2.5 text-sm font-semibold text-paper hover:bg-ink-2 disabled:opacity-70">
            {processing && <Spinner />} {children}
        </button>
    );
}

export default function Edit() {
    const { auth } = usePage().props;
    const user = auth.user;
    const profile = useForm({ name: user.name, username: user.username, email: user.email });
    const password = useForm({ current_password: '', password: '', password_confirmation: '' });
    const photo = useForm({ photo: null });

    return (
        <AppLayout title="Profil">
            <h1 className="font-display text-3xl font-bold">Profil</h1>
            <p className="mb-6 text-muted">Perbarui identitas, foto, dan keamanan akun.</p>

            <div className="grid gap-6 lg:grid-cols-2">
                <Card title="Informasi akun">
                    <form onSubmit={(e) => { e.preventDefault(); profile.put('/profile', { preserveScroll: true }); }} className="space-y-4">
                        <Field label="Nama" id="profile-name" error={profile.errors.name}>
                            <input id="profile-name" value={profile.data.name} onChange={(e) => profile.setData('name', e.target.value)} className={inputClass} />
                        </Field>
                        <Field label="Username" id="profile-username" error={profile.errors.username}>
                            <input id="profile-username" value={profile.data.username} onChange={(e) => profile.setData('username', e.target.value)} className={inputClass} />
                        </Field>
                        <Field label="Email" id="profile-email" error={profile.errors.email}>
                            <input id="profile-email" type="email" value={profile.data.email} onChange={(e) => profile.setData('email', e.target.value)} className={inputClass} />
                        </Field>
                        <Submit processing={profile.processing}>Simpan profil</Submit>
                    </form>
                </Card>

                <Card title="Foto profil">
                    <div className="mb-4 flex items-center gap-4">
                        {user.photo_url ? (
                            <img src={user.photo_url} alt="Foto profil" className="h-20 w-20 rounded-full object-cover" />
                        ) : (
                            <span className="grid h-20 w-20 place-items-center rounded-full bg-ink font-display text-3xl font-bold text-paper">{user.name.charAt(0).toUpperCase()}</span>
                        )}
                        <p className="text-sm text-muted">JPG, PNG, atau WEBP. Maksimal 2 MB.</p>
                    </div>
                    <form onSubmit={(e) => { e.preventDefault(); photo.post('/profile/photo', { forceFormData: true, preserveScroll: true, onSuccess: () => photo.reset() }); }} className="space-y-3">
                        <Field label="Pilih foto" id="profile-photo" error={photo.errors.photo}>
                            <input id="profile-photo" type="file" accept="image/*" onChange={(e) => photo.setData('photo', e.target.files[0])} className="block w-full text-sm" />
                        </Field>
                        <Submit processing={photo.processing}>Unggah foto</Submit>
                    </form>
                </Card>

                <Card title="Ubah password">
                    <form onSubmit={(e) => { e.preventDefault(); password.put('/profile/password', { preserveScroll: true, onSuccess: () => password.reset() }); }} className="space-y-4">
                        <Field label="Password saat ini" id="current-password" error={password.errors.current_password}>
                            <input id="current-password" type="password" value={password.data.current_password} onChange={(e) => password.setData('current_password', e.target.value)} className={inputClass} />
                        </Field>
                        <Field label="Password baru" id="new-password" error={password.errors.password}>
                            <input id="new-password" type="password" value={password.data.password} onChange={(e) => password.setData('password', e.target.value)} className={inputClass} />
                        </Field>
                        <Field label="Konfirmasi password baru" id="confirm-password">
                            <input id="confirm-password" type="password" value={password.data.password_confirmation} onChange={(e) => password.setData('password_confirmation', e.target.value)} className={inputClass} />
                        </Field>
                        <Submit processing={password.processing}>Ubah password</Submit>
                    </form>
                </Card>
            </div>
        </AppLayout>
    );
}
