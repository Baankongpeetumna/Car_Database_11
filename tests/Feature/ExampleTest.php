<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    // หน้าแรกของร้านเปิดได้ และหน้ารายการรถยังเปิดได้ตามเดิม
    public function test_home_page_and_car_list_are_reachable(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('products.index'), false);

        $this->get('/products')->assertOk();
    }
}
