<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Car;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CarManagementTest extends TestCase
{
    use CreatesCatalogData;
    use RefreshDatabase;

    // PNG ขนาด 1x1 จริง (เครื่องนี้ไม่มี GD จึงใช้ UploadedFile::fake()->image() ไม่ได้)
    private const TINY_PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    private Brand $brand;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->brand = Brand::create(['brand_name' => 'Toyota', 'country' => 'Japan']);
        $this->category = Category::create(['category_name' => 'SUV']);
    }

    private function pngUpload(string $name = 'car.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode(self::TINY_PNG));
    }

    private function validInput(array $overrides = []): array
    {
        return array_merge([
            'model_name' => 'Fortuner 2.8',
            'model_year' => 2024,
            'color' => 'Black',
            'fuel_type' => 'Diesel',
            'transmission' => 'Automatic',
            'car_condition' => 'New',
            'engine_cc' => 2755,
            'mileage_km' => 0,
            'price' => '1759000.50',
            'stock_qty' => 4,
            'description' => 'Test car',
            'brand_id' => $this->brand->brand_id,
            'category_id' => $this->category->category_id,
        ], $overrides);
    }

    private function storedPath(?string $imageUrl): string
    {
        return Str::after((string) $imageUrl, '/storage/');
    }

    public function test_guest_and_member_cannot_manage_cars(): void
    {
        $this->get(route('admin.cars.index'))
            ->assertRedirect(route('login'));

        $this->actingAs($this->makeUser('member'));

        $this->get(route('admin.cars.index'))->assertForbidden();
        $this->post(route('admin.cars.store'), $this->validInput())->assertForbidden();

        $this->assertSame(0, Car::count());
    }

    public function test_admin_can_create_car_with_image(): void
    {
        $this->actingAs($this->makeUser('admin'));

        $this->get(route('admin.cars.create'))->assertOk();

        $this->post(route('admin.cars.store'), $this->validInput([
            'image' => $this->pngUpload(),
        ]))->assertRedirect(route('admin.cars.index'));

        $car = Car::where('model_name', 'Fortuner 2.8')->firstOrFail();

        $this->assertSame('1759000.50', $car->price);
        $this->assertSame(4, (int) $car->stock_qty);
        $this->assertStringStartsWith('/storage/cars/', $car->image_url);
        Storage::disk('public')->assertExists($this->storedPath($car->image_url));
    }

    public function test_updating_image_replaces_old_file_and_can_remove_it(): void
    {
        $this->actingAs($this->makeUser('admin'));

        $this->post(route('admin.cars.store'), $this->validInput([
            'image' => $this->pngUpload('first.png'),
        ]));
        $car = Car::firstOrFail();
        $firstPath = $this->storedPath($car->image_url);

        // อัปโหลดรูปใหม่: ไฟล์เก่าต้องถูกลบ
        $this->put(route('admin.cars.update', $car), $this->validInput([
            'stock_qty' => 0,
            'image' => $this->pngUpload('second.png'),
        ]))->assertRedirect(route('admin.cars.index'));

        $car->refresh();
        $secondPath = $this->storedPath($car->image_url);

        $this->assertSame(0, (int) $car->stock_qty);
        $this->assertNotSame($firstPath, $secondPath);
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($secondPath);

        // แก้ข้อมูลอื่นโดยไม่ส่งรูป: รูปเดิมต้องอยู่
        $this->put(route('admin.cars.update', $car), $this->validInput(['color' => 'White']));
        $this->assertSame($car->image_url, $car->fresh()->image_url);

        // ติ๊ก remove_image: รูปหายทั้งใน DB และไฟล์
        $this->put(route('admin.cars.update', $car), $this->validInput(['remove_image' => '1']));

        $this->assertNull($car->fresh()->image_url);
        Storage::disk('public')->assertMissing($secondPath);
    }

    public function test_car_input_is_validated(): void
    {
        $this->actingAs($this->makeUser('admin'));

        $this->post(route('admin.cars.store'), $this->validInput([
            'model_name' => '',
            'model_year' => 1800,
            'fuel_type' => 'Steam',
            'transmission' => 'CVT',
            'car_condition' => 'Broken',
            'engine_cc' => -1,
            'price' => '100.123',
            'stock_qty' => -5,
            'brand_id' => 999999,
            'category_id' => 999999,
            'image' => UploadedFile::fake()->createWithContent('notes.txt', 'not an image'),
        ]))->assertSessionHasErrors([
            'model_name', 'model_year', 'fuel_type', 'transmission', 'car_condition',
            'engine_cc', 'price', 'stock_qty', 'brand_id', 'category_id', 'image',
        ]);

        $this->post(route('admin.cars.store'), $this->validInput(['price' => '0']))
            ->assertSessionHasErrors('price');

        $this->assertSame(0, Car::count());
    }

    public function test_admin_can_search_and_filter_cars(): void
    {
        $this->actingAs($this->makeUser('admin'));

        $honda = Brand::create(['brand_name' => 'Honda', 'country' => 'Japan']);
        $sedan = Category::create(['category_name' => 'Sedan']);

        Car::create($this->validInput(['model_name' => 'Fortuner', 'stock_qty' => 3]));
        Car::create($this->validInput(['model_name' => 'Civic', 'brand_id' => $honda->brand_id, 'category_id' => $sedan->category_id]));
        Car::create($this->validInput(['model_name' => 'Hilux Sold Out', 'stock_qty' => 0]));

        $this->get(route('admin.cars.index', ['q' => 'civ']))
            ->assertOk()->assertSee('Civic')->assertDontSee('Fortuner');

        $this->get(route('admin.cars.index', ['q' => 'honda']))
            ->assertSee('Civic')->assertDontSee('Fortuner');

        $this->get(route('admin.cars.index', ['brand' => $this->brand->brand_id]))
            ->assertSee('Fortuner')->assertDontSee('Civic');

        $this->get(route('admin.cars.index', ['category' => $sedan->category_id]))
            ->assertSee('Civic')->assertDontSee('Fortuner');

        $this->get(route('admin.cars.index', ['stock' => 'out']))
            ->assertSee('Hilux Sold Out')->assertDontSee('Fortuner')->assertDontSee('Civic');

        $this->get(route('admin.cars.index', ['stock' => 'in', 'brand' => $this->brand->brand_id]))
            ->assertSee('Fortuner')->assertDontSee('Hilux Sold Out')->assertDontSee('Civic');
    }

    public function test_admin_can_quickly_add_stock(): void
    {
        $car = Car::create($this->validInput(['stock_qty' => 0]));

        // member ทำไม่ได้
        $this->actingAs($this->makeUser('member'))
            ->patch(route('admin.cars.stock', $car), ['amount' => 5])
            ->assertForbidden();

        $this->actingAs($this->makeUser('admin'));

        $this->from(route('admin.cars.index'))
            ->patch(route('admin.cars.stock', $car), ['amount' => 3])
            ->assertRedirect(route('admin.cars.index'))
            ->assertSessionHas('success');

        $this->assertSame(3, (int) $car->fresh()->stock_qty);

        // บวกเพิ่มจากของเดิม ไม่ใช่แทนที่
        $this->patch(route('admin.cars.stock', $car), ['amount' => 2]);
        $this->assertSame(5, (int) $car->fresh()->stock_qty);

        // ค่าที่ไม่ถูกต้อง
        $this->patch(route('admin.cars.stock', $car), ['amount' => 0])->assertSessionHasErrors('amount');
        $this->patch(route('admin.cars.stock', $car), ['amount' => 'abc'])->assertSessionHasErrors('amount');
        $this->patch(route('admin.cars.stock', $car), ['amount' => 99999])->assertSessionHasErrors('amount');

        $this->assertSame(5, (int) $car->fresh()->stock_qty);
    }

    public function test_unused_car_can_be_deleted_with_its_image(): void
    {
        $this->actingAs($this->makeUser('admin'));

        $this->post(route('admin.cars.store'), $this->validInput(['image' => $this->pngUpload()]));
        $car = Car::firstOrFail();
        $path = $this->storedPath($car->image_url);

        $this->delete(route('admin.cars.destroy', $car))
            ->assertRedirect(route('admin.cars.index'));

        $this->assertDatabaseMissing('CAR', ['car_id' => $car->car_id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_car_in_order_cart_or_review_cannot_be_deleted(): void
    {
        $this->actingAs($this->makeUser('admin'));
        $member = $this->makeUser('member');

        $inOrder = Car::create($this->validInput(['model_name' => 'In Order']));
        $inCart = Car::create($this->validInput(['model_name' => 'In Cart']));
        $reviewed = Car::create($this->validInput(['model_name' => 'Reviewed']));

        $orderId = DB::table('ORDERS')->insertGetId([
            'status' => 'completed',
            'payment_method' => 'cash',
            'shipping_address' => 'Bangkok',
            'subtotal' => '1759000.50',
            'total_amount' => '1759000.50',
            'member_id' => $member->member_id,
        ], 'order_id');
        DB::table('ORDER_ITEM')->insert([
            'order_id' => $orderId, 'car_id' => $inOrder->car_id, 'quantity' => 1, 'unit_price' => '1759000.50',
        ]);

        $cartId = DB::table('CART')->insertGetId(['member_id' => $member->member_id], 'cart_id');
        DB::table('CART_ITEM')->insert(['cart_id' => $cartId, 'car_id' => $inCart->car_id, 'quantity' => 1]);

        DB::table('REVIEW')->insert([
            'comment' => 'Great car', 'member_id' => $member->member_id, 'car_id' => $reviewed->car_id,
        ]);

        foreach ([$inOrder, $inCart, $reviewed] as $car) {
            $this->from(route('admin.cars.index'))
                ->delete(route('admin.cars.destroy', $car))
                ->assertRedirect(route('admin.cars.index'))
                ->assertSessionHasErrors('car');

            $this->assertDatabaseHas('CAR', ['car_id' => $car->car_id]);
        }
    }

    public function test_brand_and_category_lists_are_sorted_by_id_and_searchable(): void
    {
        $this->actingAs($this->makeUser('admin'));

        // สร้างชื่อที่เรียงตามตัวอักษรกลับกับลำดับ ID
        Brand::create(['brand_name' => 'Audi', 'country' => 'Germany']);
        Category::create(['category_name' => 'Coupe']);

        // Toyota (id น้อยกว่า) ต้องมาก่อน Audi
        $this->get(route('admin.brands.index'))
            ->assertSeeInOrder(['Toyota', 'Audi']);

        $this->get(route('admin.brands.index', ['q' => 'germ']))
            ->assertSee('Audi')->assertDontSee('Toyota');

        $this->get(route('admin.categories.index'))
            ->assertSeeInOrder(['SUV', 'Coupe']);

        $this->get(route('admin.categories.index', ['q' => 'cou']))
            ->assertSee('Coupe')->assertDontSee('SUV');
    }
}
