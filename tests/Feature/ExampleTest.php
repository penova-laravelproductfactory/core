<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The root URL hands visitors to the panel (which in turn sends
     * guests to the Core\Auth login page).
     */
    public function test_the_root_url_redirects_to_the_panel(): void
    {
        $this->get('/')->assertRedirect('/admin');
    }
}
