export default function PostStatusBadge({ published }) {
    return (
        <span className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ${published ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'}`}>
            {published ? 'Dipublikasikan' : 'Draf'}
        </span>
    );
}
