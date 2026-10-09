<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Admin\CreatesCatalogData;
use Tests\TestCase;

class CarDetailPageTest extends TestCase
{
    use CreatesCatalogData;
    use RefreshDatabase;

    public function test_guest_can_view_car_details(): void
    {
        $car = $this->makeCar();

        $this->get(route('products.show', $car))
            ->assertOk()
            ->assertSee($car->model_name)
            ->assertSee('1,500,000.00')
            ->assertSee('Diesel')
            ->assertSee('2,800 cc')
            ->assertSee('Log in to buy')
            ->assertDontSee('Add to Cart')
            ->assertSee('No reviews yet.');
    }

    public function test_products_list_links_to_detail_page(): void
    {
        $car = $this->makeCar();

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee(route('products.show', $car), false);
    }

    public function test_member_sees_add_to_cart_and_admin_sees_edit_link(): void
    {
        $car = $this->makeCar();

        $this->actingAs($this->makeUser('member'))
            ->get(route('products.show', $car))
            ->assertSee('Add to Cart')
            ->assertDontSee('Edit in Admin Panel');

        $this->actingAs($this->makeUser('admin'))
            ->get(route('products.show', $car))
            ->assertDontSee('Add to Cart')
            ->assertSee('Edit in Admin Panel');
    }

    public function test_out_of_stock_car_has_no_add_to_cart_button(): void
    {
        $car = $this->makeCar();
        $car->update(['stock_qty' => 0]);

        $this->actingAs($this->makeUser('member'))
            ->get(route('products.show', $car))
            ->assertSee('Out of stock')
            ->assertDontSee('Add to Cart');
    }

    public function test_reviews_are_listed_without_full_last_name(): void
    {
        $car = $this->makeCar();

        $member = $this->makeUser('member');
        $member->forceFill(['first_name' => 'Somchai', 'last_name' => 'Jaidee'])->save();

        DB::table('REVIEW')->insert([
            'comment' => 'Smooth and quiet ride',
            'member_id' => $member->member_id,
            'car_id' => $car->car_id,
        ]);

        $this->get(route('products.show', $car))
            ->assertSee('Reviews (1)')
            ->assertSee('Smooth and quiet ride')
            ->assertSee('Somchai')
            ->assertSee('J.')
            ->assertDontSee('Jaidee');
    }

    public function test_missing_car_returns_404(): void
    {
        $this->get('/products/999999')->assertNotFound();
    }
}
