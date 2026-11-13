# BlogVerse Pro — ifs24046-pabwe2026-sk-p8-laravel-inertia

Platform blog multi-tier. Stack: **Laravel 13 · Inertia.js 2 · React 19 · PostgreSQL · Tailwind 4 · PhpSpreadsheet · TOAST UI Editor · OpenAI-compatible API**. Arsitektur Feature-First (`app/Features/{Admin,Ai,Auth,Post,Profile}`).

## Tiga tingkatan akses

| Tingkat | URL | Hak akses |
|---|---|---|
| Publik | `/`, `/articles`, `/articles/{slug}`, `/login`, `/register` | Baca, cari, filter kategori (tanpa login, tanpa sidebar) |
| Anggota | `/dashboard/posts`, `/dashboard/posts/{post}` | CRUD artikel sendiri, draf/publikasi, cover, Excel, AI |
| Admin | `/admin/users`, `/admin/posts`, `/admin/users/{user}/posts` | Monitoring read-only + ekspor Excel |

Akun seeder: `admin / admin` dan `member / member`.

## Menjalankan lokal

```bash
psql -U postgres -c "CREATE DATABASE pabwe_p8_blog;"
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
php artisan serve
```

Isi `OPENAI_KEY` (dan `OPENAI_ENDPOINT`, `OPENAI_MODEL`) di `.env` agar fitur AI aktif.

## Pengujian

```bash
php artisan test            # SQLite in-memory
composer audit
```
