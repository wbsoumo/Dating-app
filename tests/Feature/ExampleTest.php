<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test the web entry points.
     */
    public function test_admin_login_page_loads_successfully(): void
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
    }

    public function test_api_documentation_page_loads_successfully(): void
    {
        $response = $this->get('/api/docs');
        $response->assertStatus(200);
    }
}
