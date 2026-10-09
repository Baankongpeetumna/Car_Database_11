<?php

namespace Tests\Feature\Admin;

use App\Models\AdminLog;
use App\Models\Brand;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLogTest extends TestCase
{
    use CreatesCatalogData;
    use RefreshDatabase;

    public function test_brand_create_update_delete_are_logged_with_admin(): void
    {
        $admin = $this->makeUser('admin');
        $this->actingAs($admin);

        $this->post(route('admin.brands.store'), ['brand_name' => 'Mazda', 'country' => 'Japan']);
        $brand = Brand::where('brand_name', 'Mazda')->firstOrFail();

        $this->put(route('admin.brands.update', $brand), ['brand_name' => 'Mazda', 'country' => 'Hiroshima, Japan']);
        $this->delete(route('admin.brands.destroy', $brand));

        $logs = AdminLog::orderBy('log_id')->get();

        $this->assertSame(['created', 'updated', 'deleted'], $logs->pluck('action')->all());
        $this->assertTrue($logs->every(fn ($log) => $log->member_id === $admin->member_id));
        $this->assertTrue($logs->every(fn ($log) => $log->subject_type === 'BRAND' && $log->subject_id === $brand->brand_id));

        // แก้: เก็บเฉพาะช่องที่เปลี่ยน พร้อมค่าเดิม → ค่าใหม่
        $this->assertSame(['country' => ['Japan', 'Hiroshima, Japan']], $logs[1]->changes);
    }

    public function test_update_without_changes_is_not_logged(): void
    {
        $this->actingAs($this->makeUser('admin'));
        $brand = Brand::create(['brand_name' => 'Honda', 'country' => 'Japan']);

        $this->put(route('admin.brands.update', $brand), ['brand_name' => 'Honda', 'country' => 'Japan']);

        $this->assertSame(0, AdminLog::count());
    }

    public function test_car_price_change_and_restock_are_logged(): void
    {
        $this->actingAs($this->makeUser('admin'));
        $car = $this->makeCar();

        $this->put(route('admin.cars.update', $car), array_merge($car->only([
            'model_name', 'model_year', 'color', 'fuel_type', 'transmission', 'car_condition',
            'engine_cc', 'mileage_km', 'stock_qty', 'brand_id', 'category_id',
        ]), ['price' => '1450000.00']))->assertSessionHasNoErrors();

        $this->patch(route('admin.cars.stock', $car), ['amount' => 4]);

        $update = AdminLog::where('action', 'updated')->firstOrFail();
        $this->assertSame(['price' => ['1500000.00', '1450000.00']], $update->changes);

        $restock = AdminLog::where('action', 'restocked')->firstOrFail();
        $this->assertSame(['stock_qty' => [1, 5]], $restock->changes);
        $this->assertSame($car->model_name, $restock->description);
    }

    public function test_deleted_review_text_is_kept_in_log(): void
    {
        $this->actingAs($this->makeUser('admin'));
        $member = $this->makeUser('member');
        $car = $this->makeCar();

        $review = Review::forceCreate([
            'rating' => 1, 'comment' => 'Spam link here', 'member_id' => $member->member_id, 'car_id' => $car->car_id,
        ]);

        $this->delete(route('admin.reviews.destroy', $review));

        $log = AdminLog::where('subject_type', 'REVIEW')->firstOrFail();

        $this->assertSame('deleted', $log->action);
        $this->assertSame(['Spam link here', null], $log->changes['comment']);
        $this->assertStringContainsString($car->model_name, $log->description);
    }

    public function test_order_status_change_is_logged(): void
    {
        $admin = $this->makeUser('admin');
        $member = $this->makeUser('member');
        $orderId = $this->makeOrder($member, $this->makeCar(), 'pending');

        $this->actingAs($admin)
            ->patch(route('admin.orders.update', $orderId), ['status' => 'processing'])
            ->assertSessionHasNoErrors();

        $log = AdminLog::where('subject_type', 'ORDERS')->firstOrFail();

        $this->assertSame('status_changed', $log->action);
        $this->assertSame($orderId, $log->subject_id);
        $this->assertSame(['status' => ['pending', 'processing']], $log->changes);

        // ส่งสถานะเดิมซ้ำ ไม่บันทึกเพิ่ม (service ไม่เปลี่ยนอะไร)
        $this->patch(route('admin.orders.update', $orderId), ['status' => 'processing']);
        $this->assertSame(1, AdminLog::where('subject_type', 'ORDERS')->count());
    }

    public function test_failed_delete_is_not_logged(): void
    {
        $this->actingAs($this->makeUser('admin'));
        $brand = Brand::create(['brand_name' => 'Toyota', 'country' => 'Japan']);
        $this->makeCar($brand);

        $this->delete(route('admin.brands.destroy', $brand))->assertSessionHasErrors('brand');

        $this->assertSame(0, AdminLog::where('action', 'deleted')->count());
    }

    public function test_only_admin_can_view_activity_log_and_filter_it(): void
    {
        $this->get(route('admin.logs.index'))->assertRedirect(route('login'));

        $this->actingAs($this->makeUser('member'))
            ->get(route('admin.logs.index'))
            ->assertForbidden();

        $this->actingAs($this->makeUser('admin'));
        $this->post(route('admin.brands.store'), ['brand_name' => 'Volvo', 'country' => 'Sweden']);
        $this->post(route('admin.categories.store'), ['category_name' => 'Wagon']);

        $this->get(route('admin.logs.index'))
            ->assertOk()
            ->assertSee('Volvo')
            ->assertSee('Wagon');

        $this->get(route('admin.logs.index', ['subject' => 'BRAND']))
            ->assertSee('Volvo')
            ->assertDontSee('Wagon');

        $this->get(route('admin.logs.index', ['q' => 'wag']))
            ->assertSee('Wagon')
            ->assertDontSee('Volvo');
    }
}
