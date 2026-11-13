import { useEffect, useRef } from 'react';

/**
 * TOAST UI Viewer untuk merender Markdown pada halaman baca/detail.
 */
export default function MarkdownViewer({ content }) {
    const host = useRef(null);

    useEffect(() => {
        let viewer;
        let cancelled = false;

        (async () => {
            const [{ default: Viewer }] = await Promise.all([
                import('@toast-ui/editor/viewer'),
                import('@toast-ui/editor/dist/toastui-editor-viewer.css'),
            ]);

            if (cancelled || !host.current) {
                return;
            }

            viewer = new Viewer({ el: host.current, initialValue: content || '' });
        })();

        return () => {
            cancelled = true;
            viewer?.destroy();
        };
    }, [content]);

    return <div ref={host} className="prose-article min-h-24" />;
}
