import Icon from './Icon';
import Spinner from './Spinner';

export default function ConfirmDialog({ open, title, message, processing, onCancel, onConfirm }) {
    if (!open) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-[80] grid place-items-center bg-ink/60 p-4" role="alertdialog" aria-modal="true" aria-labelledby="confirm-title">
            <div className="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                <div className="mb-4 grid h-12 w-12 place-items-center rounded-full bg-red-100 text-red-600">
                    <Icon name="trash" />
                </div>
                <h2 id="confirm-title" className="font-display text-xl font-bold">
                    {title}
                </h2>
                <p className="mt-2 text-muted">{message}</p>
                <div className="mt-6 flex justify-end gap-2">
                    <button type="button" onClick={onCancel} className="rounded-xl px-4 py-2.5 text-sm font-semibold hover:bg-paper-2">
                        Batal
                    </button>
                    <button
                        type="button"
                        id="confirm-delete"
                        onClick={onConfirm}
                        disabled={processing}
                        className="flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-70"
                    >
                        {processing && <Spinner />} Hapus
                    </button>
                </div>
            </div>
        </div>
    );
}
