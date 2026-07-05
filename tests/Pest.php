<?php

use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Pest Configuration
|--------------------------------------------------------------------------
| Binds the Laravel TestCase to every Pest-style test in these folders.
| Class-based PHPUnit tests (AuthTest, …) keep working unchanged — Pest
| runs both styles side by side.
*/

uses(TestCase::class)->in('Feature');
