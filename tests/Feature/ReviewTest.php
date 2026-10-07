<?php

namespace Tests\Feature;

use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Admin\CreatesCatalogData;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use CreatesCatalogData;
    use RefreshDatabase;

    private function reviewInput(array $overrides = []): array
    {
        return array_merge(['rating' => 4, 'comment' => 'Comfortable and fuel efficient.'], $overrides);
    }

    public function test_member_with_completed_order_can_write_review(): void
    {
        $member = $this->makeUser('member');
        $car = $this->makeCar();
        $this->makeOrder($member, $car, 'completed');

        $this->actingAs($member)
            ->get(route('products.show', $car))
            ->assertSee('Write a review');

        $this->post(route('reviews.store', $car), $this->reviewInput())
            ->assertRedirect(route('products.show', $car).'#reviews');

        $this->assertDatabaseHas('REVIEW', [
            'member_id' => $member->member_id,
            'car_id' => $car->car_id,
            'rating' => 4,
            'comment' => 'Comfortable and fuel efficient.',
        ]);
    }

    public function test_cannot_review_without_completed_order(): void
    {
        $member = $this->makeUser('member');
        $car = $this->makeCar();

        // ไม่เคยซื้อเลย
        $this->actingAs($member)
            ->post(route('reviews.store', $car), $this->reviewInput())
            ->assertSessionHasErrors('review');

        // มีออเดอร์ แต่ยังไม่ completed หรือถูกยกเลิก
        foreach (['pending', 'processing', 'cancelled'] as $status) {
            $this->makeOrder($member, $car, $status);
        }

        $this->get(route('products.show', $car))->assertDontSee('Write a review');
        $this->post(route('reviews.store', $car), $this->reviewInput())
            ->assertSessionHasErrors('review');

        // ซื้อรถคันอื่นแล้ว ก็ยังรีวิวคันนี้ไม่ได้
        $this->makeOrder($member, $this->makeCar(), 'completed');
        $this->post(route('reviews.store', $car), $this->reviewInput())
            ->assertSessionHasErrors('review');

        $this->assertSame(0, Review::count());
    }

    public function test_one_review_per_completed_order(): void
    {
        $member = $this->makeUser('member');
        $car = $this->makeCar();
        $this->makeOrder($member, $car);

        $this->actingAs($member);
        $this->post(route('reviews.store', $car), $this->reviewInput())->assertSessionHasNoErrors();
        $this->post(route('reviews.store', $car), $this->reviewInput())->assertSessionHasErrors('review');

        // ซื้อรถคันเดิมอีกออเดอร์ รีวิวได้อีก 1 ครั้ง
        $this->makeOrder($member, $car);
        $this->post(route('reviews.store', $car), $this->reviewInput(['rating' => 5]))->assertSessionHasNoErrors();

        $this->assertSame(2, Review::where('car_id', $car->car_id)->count());
    }

    public function test_review_input_is_validated(): void
    {
        $member = $this->makeUser('member');
        $car = $this->makeCar();
        $this->makeOrder($member, $car);

        $this->actingAs($member)
            ->post(route('reviews.store', $car), ['rating' => 6, 'comment' => ''])
            ->assertSessionHasErrors(['rating', 'comment']);

        $this->post(route('reviews.store', $car), ['rating' => 0, 'comment' => str_repeat('a', 2001)])
            ->assertSessionHasErrors(['rating', 'comment']);

        $this->assertSame(0, Review::count());
    }

    public function test_admin_and_guest_cannot_write_reviews(): void
    {
        $car = $this->makeCar();

        $this->post(route('reviews.store', $car), $this->reviewInput())
            ->assertRedirect(route('login'));

        $this->actingAs($this->makeUser('admin'))
            ->post(route('reviews.store', $car), $this->reviewInput())
            ->assertForbidden();

        $this->assertSame(0, Review::count());
    }

    public function test_member_can_edit_and_delete_only_own_review(): void
    {
        $owner = $this->makeUser('member');
        $other = $this->makeUser('member');
        $car = $this->makeCar();

        $review = Review::forceCreate([
            'rating' => 3, 'comment' => 'Okay', 'member_id' => $owner->member_id, 'car_id' => $car->car_id,
        ]);

        // คนอื่นแก้/ลบไม่ได้
        $this->actingAs($other);
        $this->get(route('reviews.edit', $review))->assertForbidden();
        $this->put(route('reviews.update', $review), $this->reviewInput())->assertForbidden();
        $this->delete(route('reviews.destroy', $review))->assertForbidden();
        $this->get(route('products.show', $car))->assertDontSee(route('reviews.edit', $review), false);

        // เจ้าของแก้ได้
        $this->actingAs($owner);
        $this->get(route('products.show', $car))->assertSee(route('reviews.edit', $review), false);
        $this->get(route('reviews.edit', $review))->assertOk()->assertSee('Okay');

        $this->put(route('reviews.update', $review), ['rating' => 5, 'comment' => 'Much better after service'])
            ->assertRedirect(route('products.show', $car).'#reviews');

        $this->assertDatabaseHas('REVIEW', [
            'review_id' => $review->review_id, 'rating' => 5, 'comment' => 'Much better after service',
        ]);

        // เจ้าของลบได้
        $this->delete(route('reviews.destroy', $review))
            ->assertRedirect(route('products.show', $car).'#reviews');

        $this->assertDatabaseMissing('REVIEW', ['review_id' => $review->review_id]);
    }

    public function test_my_orders_links_to_review_until_car_is_reviewed(): void
    {
        $member = $this->makeUser('member');
        $car = $this->makeCar();
        $completedId = $this->makeOrder($member, $car, 'completed');
        $pendingId = $this->makeOrder($member, $this->makeCar(), 'pending');

        $this->actingAs($member);

        // หน้ารายการ: เฉพาะออเดอร์ completed มีป้าย
        $this->get(route('orders.index'))
            ->assertOk()
            ->assertSee('Review available');

        // ออเดอร์ completed: มีปุ่มไปหน้าฟอร์มรีวิว
        $this->get(route('orders.show', $completedId))
            ->assertOk()
            ->assertSee('Write a review')
            ->assertSee(route('products.show', $car).'#write-review', false);

        // ออเดอร์ pending: ยังรีวิวไม่ได้
        $this->get(route('orders.show', $pendingId))
            ->assertOk()
            ->assertDontSee('Write a review')
            ->assertSee('You can review these cars once the order is completed.');

        // รีวิวแล้ว: ปุ่มเปลี่ยนเป็น Reviewed และป้ายหายไป
        $this->post(route('reviews.store', $car), $this->reviewInput());

        $this->get(route('orders.show', $completedId))
            ->assertDontSee('Write a review')
            ->assertSee('Reviewed');

        $this->get(route('orders.index'))
            ->assertDontSee('Review available');
    }

    public function test_car_page_shows_average_rating(): void
    {
        $car = $this->makeCar();
        $member = $this->makeUser('member');

        foreach ([5, 4, 4] as $rating) {
            Review::forceCreate([
                'rating' => $rating, 'comment' => "Rated {$rating}", 'member_id' => $member->member_id, 'car_id' => $car->car_id,
            ]);
        }

        // รีวิวเก่าที่ไม่มีคะแนนไม่นับในค่าเฉลี่ย
        Review::forceCreate([
            'rating' => null, 'comment' => 'Old review', 'member_id' => $member->member_id, 'car_id' => $car->car_id,
        ]);

        $this->get(route('products.show', $car))
            ->assertSee('4.3 out of 5')
            ->assertSee('from 3 ratings')
            ->assertSee('Reviews (4)');
    }
}
