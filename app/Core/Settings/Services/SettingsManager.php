<?php

namespace App\Core\Settings\Services;

use App\Core\Settings\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Core\Settings — cached read/write access to runtime settings.
 *
 * Two configuration layers, by design:
 *   - config/penova.php  → deploy-time, developer-owned, in git
 *   - SettingsManager    → runtime, admin-owned, in the database
 *
 * Registered as a singleton by PenovaCoreServiceProvider; resolve it
 * anywhere with app(SettingsManager::class) — Modules included.
 */
class SettingsManager
{
    private const CACHE_KEY = 'penova.settings';

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()->get($key, $default);
    }

    public function set(string $key, mixed $value, string $group = 'general'): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group],
        );

        $this->flush();
    }

    /** All settings as a flat key => value collection (cached). */
    public function all(): Collection
    {
        return Cache::rememberForever(
            self::CACHE_KEY,
            fn () => Setting::query()->pluck('value', 'key'),
        );
    }

    /** Settings of one group, e.g. group('mail'). */
    public function group(string $group): Collection
    {
        return Setting::where('group', $group)->pluck('value', 'key');
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
