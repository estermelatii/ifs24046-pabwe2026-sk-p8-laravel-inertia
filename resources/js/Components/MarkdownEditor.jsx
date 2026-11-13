import { useEffect, useRef } from 'react';

/**
 * TOAST UI Editor untuk input Markdown. Dimuat lazy agar halaman lain tetap ringan.
 */
export default function MarkdownEditor({ value, onChange, height = '420px' }) {
    const host = useRef(null);
    const onChangeRef = useRef(onChange);
    onChangeRef.current = onChange;

    useEffect(() => {
        let editor;
        let cancelled = false;

        (async () => {
            const [{ default: Editor }] = await Promise.all([
                import('@toast-ui/editor'),
                import('@toast-ui/editor/dist/toastui-editor.css'),
            ]);

            if (cancelled || !host.current) {
                return;
            }

            editor = new Editor({
                el: host.current,
                height,
                initialEditType: 'markdown',
                previewStyle: 'tab',
                initialValue: value || '',
                usageStatistics: false,
                placeholder: 'Tulis artikel dengan format Markdown...',
                events: {
                    change: () => onChangeRef.current(editor.getMarkdown()),
                },
            });
        })();

        return () => {
            cancelled = true;
            editor?.destroy();
        };
        // Editor dibuat sekali; nilai awal dikirim saat mount.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    return <div ref={host} id="markdown-editor" className="overflow-hidden rounded-xl border border-line bg-white" />;
}
