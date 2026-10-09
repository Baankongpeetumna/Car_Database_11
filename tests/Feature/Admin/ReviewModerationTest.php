<?php

namespace Tests\Feature\Admin;

use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewModerationTest extends TestCase
{
    use CreatesCatalogData;
    use RefreshDatabase;

    private function makeReview(int $rating, string $comment): Review
    {
        $member = $this->makeUser('member');

        return Review::forceCreate([
            'rating' => $rating,
            'comment' => $comment,
            'member_id' => $member->member_id,
            'car_id' => $this->makeCar()->car_id,
        ]);
    }

    public function test_guest_and_member_cannot_moderate_reviews(): void
    {
        $review = $this->makeReview(1, 'Spam spam');

        $this->get(route('admin.reviews.index'))->assertRedirect(route('login'));

        $this->actingAs($this->makeUser('member'));
        $this->get(route('admin.reviews.index'))->assertForbidden();
        $this->delete(route('admin.reviews.destroy', $review))->assertForbidden();

        $this->assertDatabaseHas('REVIEW', ['review_id' => $review->review_id]);
    }

    public function test_admin_can_list_filter_and_delete_reviews(): void
    {
        $spam = $this->makeReview(1, 'Buy cheap watches here');
        $good = $this->makeReview(5, 'Excellent car');

        $this->actingAs($this->makeUser('admin'));

        $this->get(route('admin.reviews.index'))
            ->assertOk()
            ->assertSee('Buy cheap watches here')
            ->assertSee('Excellent car');

        $this->get(route('admin.reviews.index', ['rating' => 1]))
            ->assertSee('Buy cheap watches here')
            ->assertDontSee('Excellent car');

        $this->get(route('admin.reviews.index', ['q' => 'watches']))
            ->assertSee('Buy cheap watches here')
            ->assertDontSee('Excellent car');

        $this->from(route('admin.reviews.index'))
            ->delete(route('admin.reviews.destroy', $spam))
            ->assertRedirect(route('admin.reviews.index'));

        $this->assertDatabaseMissing('REVIEW', ['review_id' => $spam->review_id]);
        $this->assertDatabaseHas('REVIEW', ['review_id' => $good->review_id]);
    }
}
