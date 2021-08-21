<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HomeControllerTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_register_page()
    {
        $response = $this->get(route('register'));
        $response->assertViewIs('auth.register');
        $response->assertSuccessful();
    }

    /**
     * @test
     */
    public function it_returns_login_page()
    {
        $response = $this->get(route('login'));
        $response->assertViewIs('auth.login');
        $response->assertSuccessful();
    }
}
