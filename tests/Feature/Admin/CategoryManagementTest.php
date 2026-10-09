<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use CreatesCatalogData;
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.categories.index'))
            ->assertRedirect(route('login'));
    }

    public function test_member_cannot_access_category_management(): void
    {
        $this->actingAs($this->makeUser('member'));

        $this->get(route('admin.categories.index'))->assertForbidden();

        $this->post(route('admin.categories.store'), [
            'category_name' => 'Hack',
        ])->assertForbidden();

        $this->assertDatabaseMissing('CATEGORY', ['category_name' => 'Hack']);
    }

    public function test_admin_can_create_update_and_delete_category(): void
    {
        $this->actingAs($this->makeUser('admin'));

        $this->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('Manage Categories');

        $this->post(route('admin.categories.store'), [
            'category_name' => 'Pickup',
        ])->assertRedirect(route('admin.categories.index'));

        $category = Category::where('category_name', 'Pickup')->firstOrFail();

        $this->put(route('admin.categories.update', $category), [
            'category_name' => 'Pickup Truck',
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('CATEGORY', [
            'category_id' => $category->category_id,
            'category_name' => 'Pickup Truck',
        ]);

        $this->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseMissing('CATEGORY', ['category_id' => $category->category_id]);
    }

    public function test_category_input_is_validated(): void
    {
        $this->actingAs($this->makeUser('admin'));
        Category::create(['category_name' => 'SUV']);

        $this->post(route('admin.categories.store'), [
            'category_name' => '',
        ])->assertSessionHasErrors('category_name');

        $this->post(route('admin.categories.store'), [
            'category_name' => str_repeat('a', 256),
        ])->assertSessionHasErrors('category_name');

        $this->post(route('admin.categories.store'), [
            'category_name' => 'SUV',
        ])->assertSessionHasErrors('category_name');

        $this->assertSame(1, Category::count());
    }

    public function test_category_with_cars_cannot_be_deleted(): void
    {
        $this->actingAs($this->makeUser('admin'));
        $category = Category::create(['category_name' => 'Sedan']);
        $this->makeCar(category: $category);

        $this->from(route('admin.categories.index'))
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHasErrors('category');

        $this->assertDatabaseHas('CATEGORY', ['category_id' => $category->category_id]);
    }
}
