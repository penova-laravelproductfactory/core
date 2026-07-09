<?php

use Tests\StoreTestCase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Pest Configuration
|--------------------------------------------------------------------------
| Binds the Laravel TestCase to every Pest-style test in these folders.
| Class-based PHPUnit tests (AuthTest, …) keep working unchanged — Pest
| runs both styles side by side.
*/

// Core lane: everything under Feature/Core runs with NO business module
// enabled (config penova.modules is empty — D-026), the honest post-decouple
// default. Class-based tests (AuthTest, ExampleTest) declare their own base.
uses(TestCase::class)->in('Feature/Core');

// Store MODULE lane: Feature/Store explicitly enables the Store module (its
// routes + migrations) via StoreTestCase — the application-level opt-in a real
// app makes when it wires Store into config/penova.php.
uses(StoreTestCase::class)->in('Feature/Store');
