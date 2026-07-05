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
            // App\Modules\Booking → BookingSeeder::class goes here later.
        ]);
    }
}
