export default function Field({ label, id, error, children, hint }) {
    return (
        <div>
            <label htmlFor={id} className="mb-1.5 block text-sm font-semibold text-ink">
                {label}
            </label>
            {children}
            {hint && !error && <p className="mt-1 text-xs text-muted">{hint}</p>}
            {error && (
                <p className="mt-1 text-sm font-medium text-danger" role="alert">
                    {error}
                </p>
            )}
        </div>
    );
}

export const inputClass =
    'w-full rounded-xl border border-line bg-white px-3.5 py-2.5 text-ink placeholder:text-muted/70 focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20';
