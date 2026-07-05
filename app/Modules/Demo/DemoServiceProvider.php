<?php

namespace App\Modules\Demo;

use App\Core\Support\PenovaModule;
use Illuminate\Support\ServiceProvider;

/**
 * Demo — the reference module. It exists to demonstrate (and regression-
 * test) the module contract end to end: an invokable-controller route,
 * a sidebar menu item, and two dashboard widgets, all declared here and
 * discovered by Core through config('penova.modules') alone.
 *
 * Building a real module? Copy this folder, rename, and start from
 * app/Modules/README.md.
 */
class DemoServiceProvider extends ServiceProvider implements PenovaModule
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }

    /** @see PenovaModule — sidebar contribution. */
    public static function menu(): array
    {
        return [
            ['key' => 'demo', 'label' => 'نمونه', 'route' => 'demo.index', 'icon' => 'sparkles', 'order' => 100],
        ];
    }

    /** @see PenovaModule — dashboard contribution. */
    public static function widgets(): array
    {
        return [
            // Own 'demo' area — the recommended pattern: one area per
            // module so its widgets group under a dedicated heading.
            ['key' => 'demo-card-one', 'type' => 'card', 'title' => 'ویجت نمونه ۱', 'component' => 'Modules/Demo/Widgets/DemoCardOne', 'cols' => 1, 'order' => 100, 'area' => 'demo'],
            ['key' => 'demo-card-two', 'type' => 'card', 'title' => 'ویجت نمونه ۲', 'component' => 'Modules/Demo/Widgets/DemoCardTwo', 'cols' => 1, 'order' => 110, 'area' => 'demo'],
        ];
    }
}
