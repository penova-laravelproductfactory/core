<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The root URL renders the Penova Core Lite welcome page (shown to
     * everyone; the page itself adapts its CTA to auth state).
     *
     * Needs a migrated DB: the shared `branding` prop (HandleInertiaRequests)
     * reads the settings table on every request, guests included.
     */
    public function test_the_root_url_renders_the_welcome_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Core/Welcome'));
    }
}
