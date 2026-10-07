<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandManagementTest extends TestCase
{
    use CreatesCatalogData;
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.brands.index'))
            ->assertRedirect(route('login'));
    }

    public function test_shop_page_shows_admin_badge_only_for_admin(): void
    {
        // ยังไม่ login ก็เปิดหน้ารถได้
        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Log in')
            ->assertDontSee('Admin Panel');

        $this->actingAs($this->makeUser('member'));
        $this->get(route('products.index'))
            ->assertOk()
            ->assertDontSee('Admin Panel');

        $this->actingAs($this->makeUser('admin'));
        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Admin Panel');
    }

    public function test_member_cannot_access_brand_management(): void
    {
        $this->actingAs($this->makeUser('member'));

        $this->get(route('admin.brands.index'))->assertForbidden();

        $this->post(route('admin.brands.store'), [
            'brand_name' => 'Hack',
            'country' => 'X',
        ])->assertForbidden();

        $this->assertDatabaseMissing('BRAND', ['brand_name' => 'Hack']);
    }

    public function test_admin_can_create_update_and_delete_brand(): void
    {
        $this->actingAs($this->makeUser('admin'));

        // หน้า admin ใช้ sidebar และมีป้ายบอกว่าเป็นบัญชี admin
        $this->get(route('admin.brands.index'))
            ->assertOk()
            ->assertSee('Admin Panel')
            ->assertSee('Admin account');

        $this->post(route('admin.brands.store'), [
            'brand_name' => 'Mazda',
            'country' => 'Japan',
        ])->assertRedirect(route('admin.brands.index'));

        $brand = Brand::where('brand_name', 'Mazda')->firstOrFail();

        $this->put(route('admin.brands.update', $brand), [
            'brand_name' => 'Mazda Motor',
            'country' => 'Japan',
        ])->assertRedirect(route('admin.brands.index'));

        $this->assertDatabaseHas('BRAND', [
            'brand_id' => $brand->brand_id,
            'brand_name' => 'Mazda Motor',
            'country' => 'Japan',
        ]);

        $this->delete(route('admin.brands.destroy', $brand))
            ->assertRedirect(route('admin.brands.index'));

        $this->assertDatabaseMissing('BRAND', ['brand_id' => $brand->brand_id]);
    }

    public function test_brand_input_is_validated(): void
    {
        $this->actingAs($this->makeUser('admin'));
        Brand::create(['brand_name' => 'Toyota', 'country' => 'Japan']);

        $this->post(route('admin.brands.store'), [
            'brand_name' => '',
            'country' => str_repeat('a', 101),
        ])->assertSessionHasErrors(['brand_name', 'country']);

        $this->post(route('admin.brands.store'), [
            'brand_name' => 'Toyota',
            'country' => 'Japan',
        ])->assertSessionHasErrors('brand_name');

        $this->assertSame(1, Brand::count());
    }

    public function test_brand_with_cars_cannot_be_deleted(): void
    {
        $this->actingAs($this->makeUser('admin'));
        $brand = Brand::create(['brand_name' => 'Toyota', 'country' => 'Japan']);
        $this->makeCar($brand);

        $this->from(route('admin.brands.index'))
            ->delete(route('admin.brands.destroy', $brand))
            ->assertRedirect(route('admin.brands.index'))
            ->assertSessionHasErrors('brand');

        $this->assertDatabaseHas('BRAND', ['brand_id' => $brand->brand_id]);
    }
}
