import { Link } from '@inertiajs/react';
import Icon from '../../Components/Icon';
import Pagination from '../../Components/Pagination';
import SearchBar from '../../Components/SearchBar';
import AppLayout from '../../Layouts/AppLayout';

export default function UserList({ users, filters }) {
    return (
        <AppLayout title="Kelola Pengguna">
            <div className="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 className="font-display text-3xl font-bold">Pengguna</h1>
                    <p className="text-muted">Pantau seluruh pengguna dan artikel mereka (read-only).</p>
                </div>
                <Link href="/admin/posts" className="flex items-center gap-2 rounded-xl bg-ink px-4 py-2.5 text-sm font-semibold text-paper hover:bg-ink-2">
                    <Icon name="layers" className="h-4 w-4" /> Semua artikel
                </Link>
            </div>

            <div className="mt-6">
                <SearchBar url="/admin/users" filters={filters} placeholder="Cari nama, username, atau email..." />
            </div>

            <div className="mt-5 overflow-x-auto rounded-2xl border border-line bg-white">
                <table className="w-full min-w-[640px] text-left text-sm">
                    <thead className="bg-paper-2 text-xs uppercase tracking-wide text-muted">
                        <tr>
                            <th className="px-5 py-3">Pengguna</th>
                            <th className="px-5 py-3">Email</th>
                            <th className="px-5 py-3">Role</th>
                            <th className="px-5 py-3 text-right">Artikel</th>
                            <th className="px-5 py-3" />
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-line">
                        {users.data.map((user) => (
                            <tr key={user.id}>
                                <td className="px-5 py-3.5">
                                    <p className="font-semibold">{user.name}</p>
                                    <p className="text-muted">@{user.username}</p>
                                </td>
                                <td className="px-5 py-3.5 text-muted">{user.email}</td>
                                <td className="px-5 py-3.5">
                                    <span className={`rounded-full px-2.5 py-0.5 text-xs font-semibold ${user.role === 'admin' ? 'bg-ink text-paper' : 'bg-paper-2'}`}>{user.role}</span>
                                </td>
                                <td className="px-5 py-3.5 text-right font-semibold">{user.posts_count}</td>
                                <td className="px-5 py-3.5 text-right">
                                    <Link href={`/admin/users/${user.id}/posts`} className="inline-flex items-center gap-1.5 font-semibold text-primary hover:underline">
                                        <Icon name="eye" className="h-4 w-4" /> Lihat artikel
                                    </Link>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <Pagination links={users.links} />
        </AppLayout>
    );
}
