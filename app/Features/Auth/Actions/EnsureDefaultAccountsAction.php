<?php

namespace App\Features\Auth\Actions;

use App\Features\Post\Services\PostService;
use App\Models\User;

/**
 * Memastikan akun bawaan tersedia: admin / admin dan member / member (sesuai seeder).
 *
 * Aman dijalankan berulang kali, termasuk pada database kosong (mis. server deploy).
 * Artikel contoh hanya dibuat saat akun member baru dibuat.
 */
class EnsureDefaultAccountsAction
{
    private const DEMO_POSTS = [
        ['Memulai Belajar Laravel 13', 'Teknologi', 'Langkah pertama membangun aplikasi web modern dengan Laravel.', "## Mengapa Laravel?\n\nLaravel menyediakan struktur yang rapi, ekosistem lengkap, dan komunitas besar.\n\n- Routing yang ekspresif\n- Eloquent ORM\n- Testing bawaan", true],
        ['Tips Produktif Saat Belajar Daring', 'Gaya Hidup', 'Atur waktu dan lingkungan belajar agar tetap fokus.', "## Mulai dari jadwal\n\nBuat blok waktu belajar yang konsisten dan hindari multitasking.", true],
        ['Draf: Rencana Konten Bulan Depan', 'Umum', 'Catatan awal sebelum diterbitkan.', 'Daftar ide artikel yang akan ditulis bulan depan.', false],
    ];

    public function __construct(private readonly PostService $posts) {}

    public function execute(): void
    {
        $this->ensure('admin', 'Administrator', 'admin@example.com', User::ROLE_ADMIN);
        $member = $this->ensure('member', 'Member Demo', 'member@example.com', User::ROLE_MEMBER);

        if ($member->wasRecentlyCreated) {
            $this->createDemoPosts($member);
        }
    }

    private function ensure(string $username, string $name, string $email, string $role): User
    {
        $user = User::firstOrNew(['username' => $username]);

        if (! $user->exists) {
            $user->name = $name;
            $user->email = User::where('email', $email)->exists() ? $username.'.'.uniqid().'@example.com' : $email;
        }

        $user->role = $role;
        $user->password = $username;
        $user->save();

        return $user;
    }

    private function createDemoPosts(User $member): void
    {
        foreach (self::DEMO_POSTS as [$title, $category, $excerpt, $content, $published]) {
            $this->posts->create($member, [
                'title' => $title,
                'category' => $category,
                'excerpt' => $excerpt,
                'content' => $content,
                'is_published' => $published,
            ]);
        }
    }
}
