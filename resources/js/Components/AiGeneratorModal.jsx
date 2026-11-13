import { router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import Field, { inputClass } from './Field';
import Icon from './Icon';
import Spinner from './Spinner';

export default function AiGeneratorModal({ generated, onClose }) {
    const form = useForm({ prompt: '', count: 3 });
    const [saving, setSaving] = useState(false);

    const generate = (event) => {
        event.preventDefault();
        form.post('/dashboard/posts/ai-generate', { preserveScroll: true });
    };

    const save = () => {
        router.post(
            '/dashboard/posts/ai-save',
            { items: generated },
            { onStart: () => setSaving(true), onFinish: () => setSaving(false), onSuccess: onClose }
        );
    };

    return (
        <div className="fixed inset-0 z-50 grid place-items-center bg-ink/60 p-4" role="dialog" aria-modal="true" aria-labelledby="ai-title">
            <div className="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
                <header className="flex items-center justify-between bg-ink px-6 py-4 text-paper">
                    <h2 id="ai-title" className="flex items-center gap-2 font-display text-lg font-bold">
                        <Icon name="sparkles" className="h-5 w-5 text-sky-300" /> Rancang Artikel dengan AI
                    </h2>
                    <button type="button" onClick={onClose} aria-label="Tutup" className="grid h-8 w-8 place-items-center rounded-lg hover:bg-white/15">
                        <Icon name="x" className="h-4 w-4" />
                    </button>
                </header>

                <div className="flex-1 space-y-5 overflow-y-auto p-6">
                    <form onSubmit={generate} className="space-y-4">
                        <Field label="Topik / instruksi" id="ai-prompt" error={form.errors.prompt}>
                            <textarea
                                id="ai-prompt"
                                rows={3}
                                value={form.data.prompt}
                                onChange={(e) => form.setData('prompt', e.target.value)}
                                className={inputClass}
                                placeholder="Contoh: tips belajar pemrograman untuk pemula"
                            />
                        </Field>
                        <div className="flex items-end gap-3">
                            <div className="w-32">
                                <Field label="Jumlah" id="ai-count" error={form.errors.count} hint="1 – 5 artikel">
                                    <input
                                        id="ai-count"
                                        type="number"
                                        min={1}
                                        max={5}
                                        value={form.data.count}
                                        onChange={(e) => form.setData('count', e.target.value)}
                                        className={inputClass}
                                    />
                                </Field>
                            </div>
                            <button
                                type="submit"
                                id="ai-generate"
                                disabled={form.processing}
                                className="mb-5 flex items-center gap-2 rounded-xl bg-accent px-5 py-2.5 text-sm font-semibold text-white hover:bg-accent-2 disabled:opacity-70"
                            >
                                {form.processing ? <Spinner /> : <Icon name="sparkles" className="h-4 w-4" />} Hasilkan
                            </button>
                        </div>
                    </form>

                    {generated && generated.length > 0 && (
                        <div>
                            <h3 className="mb-2 text-sm font-semibold uppercase tracking-wide text-muted">Hasil ({generated.length} draf)</h3>
                            <ul className="space-y-2">
                                {generated.map((item, index) => (
                                    <li key={index} className="rounded-xl border border-line bg-paper p-3.5">
                                        <p className="font-semibold">{item.title}</p>
                                        <p className="text-xs font-semibold uppercase tracking-wide text-accent">{item.category}</p>
                                        {item.excerpt && <p className="mt-1 text-sm text-muted">{item.excerpt}</p>}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                </div>

                {generated && generated.length > 0 && (
                    <footer className="flex justify-end gap-3 bg-accent-soft px-6 py-4">
                        <button type="button" onClick={onClose} className="rounded-xl px-5 py-2.5 text-sm font-semibold hover:bg-black/5">
                            Tutup
                        </button>
                        <button
                            type="button"
                            id="ai-save"
                            onClick={save}
                            disabled={saving}
                            className="flex items-center gap-2 rounded-xl bg-ink px-5 py-2.5 text-sm font-semibold text-paper hover:bg-ink-2 disabled:opacity-70"
                        >
                            {saving && <Spinner />} Simpan semua sebagai draf
                        </button>
                    </footer>
                )}
            </div>
        </div>
    );
}
