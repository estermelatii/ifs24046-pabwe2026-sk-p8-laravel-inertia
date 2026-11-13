import { router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

/**
 * Top progress bar + floating pill "Memproses tindakan..." untuk setiap navigasi/request Inertia.
 */
export default function ProgressIndicator() {
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        const offStart = router.on('start', () => setBusy(true));
        const offFinish = router.on('finish', () => setBusy(false));

        return () => {
            offStart();
            offFinish();
        };
    }, []);

    if (!busy) {
        return null;
    }

    return (
        <>
            <div className="top-progress" role="progressbar" aria-label="Memuat halaman" />
            <div
                role="status"
                className="fixed bottom-5 left-1/2 z-[100] flex -translate-x-1/2 items-center gap-2.5 rounded-full bg-ink px-4 py-2.5 text-sm font-medium text-paper shadow-xl"
            >
                <span className="spinner" />
                Memproses tindakan...
            </div>
        </>
    );
}
