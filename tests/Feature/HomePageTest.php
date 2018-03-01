<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HomePageTest extends TestCase
{
    /** @test */
    public function we_can_get_the_homepage()
    {
        $response = $this->get('/');

        $response->assertSuccessful();
    }
}
