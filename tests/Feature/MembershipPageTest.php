<?php

namespace Tests\Feature;

use App\Models\MembershipTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Admin\CreatesCatalogData;
use Tests\TestCase;

class MembershipPageTest extends TestCase
{
    use CreatesCatalogData;
    use RefreshDatabase;

    private MembershipTier $silver;
    private MembershipTier $gold;

    protected function setUp(): void
    {
        parent::setUp();

        // สร้าง tier เริ่มต้นก่อน makeUser() จะได้ใช้ตัวนี้
        MembershipTier::create(['tier_name' => 'Basic', 'min_points' => 0, 'discount_percent' => 0]);
        $this->silver = MembershipTier::create(['tier_name' => 'Silver', 'min_points' => 500, 'discount_percent' => 3]);
        $this->gold = MembershipTier::create(['tier_name' => 'Gold', 'min_points' => 2000, 'discount_percent' => '5.50']);
    }

    public function test_guest_sees_all_tiers_and_discounts(): void
    {
        $this->get(route('membership.index'))
            ->assertOk()
            ->assertSeeInOrder(['Basic', 'Silver', 'Gold'])
            ->assertSee('3%')
            ->assertSee('5.5%')
            ->assertSee('From 2,000 points')
            ->assertSee('Create an account')
            ->assertDontSee('Your tier');
    }

    public function test_member_sees_progress_to_next_tier(): void
    {
        $member = $this->makeUser('member');
        $member->forceFill(['points' => 1800, 'tier_id' => $this->silver->tier_id])->save();

        $this->actingAs($member)
            ->get(route('membership.index'))
            ->assertOk()
            ->assertSee('Your tier')
            ->assertSee('1,800')
            ->assertSee('200')
            ->assertSee('฿200,000')
            ->assertSee('to reach')
            ->assertSee('Gold');
    }

    public function test_member_at_top_tier_sees_message(): void
    {
        $member = $this->makeUser('member');
        $member->forceFill(['points' => 2500, 'tier_id' => $this->gold->tier_id])->save();

        $this->actingAs($member)
            ->get(route('membership.index'))
            ->assertSee('You are at the highest tier.')
            ->assertDontSee('to reach');
    }

    public function test_admin_sees_tiers_without_progress(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->get(route('membership.index'))
            ->assertOk()
            ->assertSee('Gold')
            ->assertDontSee('Your points');
    }
}
