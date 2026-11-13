<?php

namespace Database\Seeders;

use App\Features\Auth\Actions\EnsureDefaultAccountsAction;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Seeder: admin / admin dan member / member (+ artikel contoh). */
    public function run(): void
    {
        app(EnsureDefaultAccountsAction::class)->execute();
    }
}
