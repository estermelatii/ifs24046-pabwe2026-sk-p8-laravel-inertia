import { useEffect, useRef, useState } from 'react';
import Icon from './Icon';

/**
 * Menu tindakan ⋮ — Detail (ikon mata, primary), Ubah (ikon pensil, primary), Hapus (ikon sampah, merah).
 */
export default function KebabMenu({ onDetail, onEdit, onDelete, label }) {
    const [open, setOpen] = useState(false);
    const ref = useRef(null);

    useEffect(() => {
        const close = (event) => {
            if (ref.current && !ref.current.contains(event.target)) {
                setOpen(false);
            }
        };
        document.addEventListener('mousedown', close);

        return () => document.removeEventListener('mousedown', close);
    }, []);

    const item = 'flex w-full items-center gap-2.5 px-3.5 py-2 text-left text-sm font-medium hover:bg-paper-2';
    const run = (fn) => () => {
        setOpen(false);
        fn();
    };

    return (
        <div ref={ref} className="relative">
            <button
                type="button"
                onClick={() => setOpen(!open)}
                aria-haspopup="menu"
                aria-expanded={open}
                aria-label={`Menu tindakan ${label}`}
                className="grid h-9 w-9 place-items-center rounded-lg text-muted hover:bg-paper-2 hover:text-ink"
            >
                <Icon name="dots" />
            </button>
            {open && (
                <div role="menu" className="absolute right-0 z-30 mt-1 w-44 overflow-hidden rounded-xl border border-line bg-white py-1 shadow-xl">
                    <button type="button" role="menuitem" onClick={run(onDetail)} className={`${item} text-primary`}>
                        <Icon name="eye" className="h-4 w-4" /> Detail
                    </button>
                    <button type="button" role="menuitem" onClick={run(onEdit)} className={`${item} text-primary`}>
                        <Icon name="pencil" className="h-4 w-4" /> Ubah
                    </button>
                    <button type="button" role="menuitem" onClick={run(onDelete)} className={`${item} text-red-600`}>
                        <Icon name="trash" className="h-4 w-4" /> Hapus
                    </button>
                </div>
            )}
        </div>
    );
}
