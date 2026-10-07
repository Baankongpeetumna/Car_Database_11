<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    // หน้าแรกพาไปหน้ารายการรถ
    public function test_home_redirects_to_car_list(): void
    {
        $this->get(route('home'))
            ->assertRedirect('/products');

        $this->get('/products')->assertOk();
    }
}
