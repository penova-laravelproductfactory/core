<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Core seeds only the Core baseline. Core names no Module seeder (D-026):
     * an application that enables a Module composes that Module's seeding
     * itself — at the application layer, the same place it wires the Module
     * into config/penova.php 'modules'. This keeps Core independent of any
     * Module (13); it is builder-owned composition, not a Core contract.
     */
    public function run(): void
    {
        $this->call([
            PenovaCoreSeeder::class,
        ]);
    }
}
