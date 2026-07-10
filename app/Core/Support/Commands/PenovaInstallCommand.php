<?php

namespace App\Core\Support\Commands;

use Illuminate\Console\Command;

/**
 * Core\Support — one-shot dev installer:
 *
 *   php artisan penova:install          # migrate + seed
 *   php artisan penova:install --fresh  # drop everything first
 *
 * Wraps the standard Laravel commands so a fresh checkout is usable in
 * one step. Production deploys keep using migrate --force directly.
 */
class PenovaInstallCommand extends Command
{
    protected $signature = 'penova:install {--fresh : Drop all tables and re-run every migration}';

    protected $description = 'Install Penova Core: run migrations and seed the Core baseline (operator role, permissions, operator user)';

    public function handle(): int
    {
        $this->components->info('Installing Penova Core…');

        $this->call($this->option('fresh') ? 'migrate:fresh' : 'migrate');
        $this->call('db:seed');

        $this->components->info(sprintf(
            'Done. Log in at /%s with %s (see docs/getting-started.md).',
            config('penova.workspace.prefix'),
            config('penova.operator.email'),
        ));

        return self::SUCCESS;
    }
}
