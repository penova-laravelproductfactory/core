<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Core baseline first; product Module seeders run after it so their
     * permissions/roles can build on the Core ones.
     */
    public function run(): void
    {
        $this->call([
            PenovaCoreSeeder::class,

            // Module permission seeders (product-level composition — the
            // same place modules get wired in as config/penova.php).
            \App\Modules\Store\Database\Seeders\StorePermissionsSeeder::class,
        ]);
    }
}
