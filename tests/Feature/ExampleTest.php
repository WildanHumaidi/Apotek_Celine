<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test root route redirects to login.
     */
    public function test_the_application_redirects_root_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    /**
     * Test login page returns a successful response.
     */
    public function test_the_login_page_returns_a_successful_response(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }
}
