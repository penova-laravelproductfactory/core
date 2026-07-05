<?php

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The root URL renders the Penova Core Lite welcome page (shown to
     * everyone; the page itself adapts its CTA to auth state).
     */
    public function test_the_root_url_renders_the_welcome_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Core/Welcome'));
    }
}
