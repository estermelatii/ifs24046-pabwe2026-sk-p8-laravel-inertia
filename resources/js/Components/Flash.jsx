import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import Icon from './Icon';

export default function Flash() {
    const { flash } = usePage().props;
    const [visible, setVisible] = useState(true);
    const message = flash.success || flash.error;

    useEffect(() => {
        setVisible(true);
        const timer = setTimeout(() => setVisible(false), 4500);

        return () => clearTimeout(timer);
    }, [message]);

    if (!message || !visible) {
        return null;
    }

    const ok = Boolean(flash.success);

    return (
        <div
            role="status"
            className={`fixed right-4 top-4 z-[90] flex max-w-sm items-start gap-3 rounded-xl px-4 py-3 text-sm font-medium shadow-lg ${ok ? 'bg-emerald-700 text-white' : 'bg-red-700 text-white'}`}
        >
            <Icon name={ok ? 'check' : 'x'} className="mt-0.5 h-4 w-4 shrink-0" />
            <span>{message}</span>
        </div>
    );
}
