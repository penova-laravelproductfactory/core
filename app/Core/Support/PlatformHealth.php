<?php

namespace App\Core\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Core\Support — a lightweight platform health snapshot for the Workspace.
 *
 * Each subsystem is probed cheaply and defensively (one try/catch each) so a
 * single failure degrades to a "warning" row instead of breaking the page.
 * Reports Ready/Warning only — not a metrics dashboard.
 */
final class PlatformHealth
{
    /** @return list<array{key: string, label: string, status: string, detail: string}> */
    public function check(): array
    {
        return [
            $this->laravel(),
            $this->database(),
            $this->storage(),
            $this->cache(),
            $this->queue(),
        ];
    }

    private function laravel(): array
    {
        return $this->item('laravel', 'Laravel', true, app()->version());
    }

    private function database(): array
    {
        try {
            DB::connection()->getPdo();
            $detail = DB::connection()->getDriverName();
            $ok = true;
        } catch (Throwable $e) {
            $detail = 'unreachable';
            $ok = false;
        }

        return $this->item('database', 'Database', $ok, $detail);
    }

    private function storage(): array
    {
        $probe = 'penova-health-'.uniqid();

        try {
            try {
                Storage::disk('local')->put($probe, 'ok');
                $ok = Storage::disk('local')->get($probe) === 'ok';
                $detail = $ok ? 'writable' : 'not writable';
            } finally {
                Storage::disk('local')->delete($probe);
            }
        } catch (Throwable $e) {
            $ok = false;
            $detail = 'not writable';
        }

        return $this->item('storage', 'Storage', $ok, $detail);
    }

    private function cache(): array
    {
        $probe = 'penova-health-'.uniqid();

        try {
            try {
                Cache::put($probe, 'ok', 5);
                $ok = Cache::get($probe) === 'ok';
                $detail = $ok ? (string) config('cache.default') : 'unreachable';
            } finally {
                Cache::forget($probe);
            }
        } catch (Throwable $e) {
            $ok = false;
            $detail = 'unreachable';
        }

        return $this->item('cache', 'Cache', $ok, $detail);
    }

    private function queue(): array
    {
        $driver = (string) config('queue.default');

        return $this->item(
            'queue',
            'Queue',
            true,
            $driver === 'sync' ? 'synchronous' : $driver,
        );
    }

    /** @return array{key: string, label: string, status: string, detail: string} */
    private function item(string $key, string $label, bool $ok, string $detail): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'status' => $ok ? 'ready' : 'warning',
            'detail' => $detail,
        ];
    }
}
