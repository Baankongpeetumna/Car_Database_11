<?php

namespace Tests\Feature\Admin;

use App\Models\MembershipTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TierManagementTest extends TestCase
{
    use CreatesCatalogData;
    use RefreshDatabase;

    private function baseTier(): MembershipTier
    {
        return MembershipTier::where('min_points', 0)->firstOrFail();
    }

    public function test_guest_and_member_cannot_manage_tiers(): void
    {
        $this->get(route('admin.tiers.index'))
            ->assertRedirect(route('login'));

        $this->actingAs($this->makeUser('member'));

        $this->get(route('admin.tiers.index'))->assertForbidden();
        $this->post(route('admin.tiers.store'), [
            'tier_name' => 'Hack', 'min_points' => 1, 'discount_percent' => 99,
        ])->assertForbidden();

        $this->assertDatabaseMissing('MEMBERSHIP_TIER', ['tier_name' => 'Hack']);
    }

    public function test_admin_can_create_update_and_delete_tier(): void
    {
        $this->actingAs($this->makeUser('admin'));

        $this->get(route('admin.tiers.index'))
            ->assertOk()
            ->assertSee('Default for new members');

        $this->post(route('admin.tiers.store'), [
            'tier_name' => 'Gold',
            'min_points' => 2000,
            'discount_percent' => '5.50',
        ])->assertRedirect(route('admin.tiers.index'));

        $tier = MembershipTier::where('tier_name', 'Gold')->firstOrFail();
        $this->assertSame('5.50', $tier->discount_percent);

        $this->put(route('admin.tiers.update', $tier), [
            'tier_name' => 'Gold Plus',
            'min_points' => 1500,
            'discount_percent' => 7,
        ])->assertRedirect(route('admin.tiers.index'));

        $this->assertDatabaseHas('MEMBERSHIP_TIER', [
            'tier_id' => $tier->tier_id,
            'tier_name' => 'Gold Plus',
            'min_points' => 1500,
        ]);

        $this->delete(route('admin.tiers.destroy', $tier))
            ->assertRedirect(route('admin.tiers.index'));

        $this->assertDatabaseMissing('MEMBERSHIP_TIER', ['tier_id' => $tier->tier_id]);
    }

    public function test_tier_input_is_validated(): void
    {
        $this->actingAs($this->makeUser('admin'));
        MembershipTier::create(['tier_name' => 'Gold', 'min_points' => 2000, 'discount_percent' => 5]);

        $this->post(route('admin.tiers.store'), [
            'tier_name' => '',
            'min_points' => -1,
            'discount_percent' => 150,
        ])->assertSessionHasErrors(['tier_name', 'min_points', 'discount_percent']);

        // ชื่อซ้ำ และ min_points ซ้ำ
        $this->post(route('admin.tiers.store'), [
            'tier_name' => 'Gold',
            'min_points' => 2000,
            'discount_percent' => 5,
        ])->assertSessionHasErrors(['tier_name', 'min_points']);

        // tier ที่ min_points = 0 มีได้ตัวเดียว
        $this->post(route('admin.tiers.store'), [
            'tier_name' => 'Another Basic',
            'min_points' => 0,
            'discount_percent' => 0,
        ])->assertSessionHasErrors('min_points');

        $this->post(route('admin.tiers.store'), [
            'tier_name' => 'Odd',
            'min_points' => 100,
            'discount_percent' => '1.234',
        ])->assertSessionHasErrors('discount_percent');

        $this->assertSame(2, MembershipTier::count());
    }

    public function test_default_tier_cannot_be_deleted_or_moved_off_zero(): void
    {
        $this->actingAs($this->makeUser('admin'));
        $base = $this->baseTier();

        $this->from(route('admin.tiers.index'))
            ->delete(route('admin.tiers.destroy', $base))
            ->assertSessionHasErrors('tier');

        $this->from(route('admin.tiers.edit', $base))
            ->put(route('admin.tiers.update', $base), [
                'tier_name' => $base->tier_name,
                'min_points' => 100,
                'discount_percent' => 0,
            ])
            ->assertSessionHasErrors('min_points');

        $this->assertDatabaseHas('MEMBERSHIP_TIER', ['tier_id' => $base->tier_id, 'min_points' => 0]);

        // แก้ชื่อและส่วนลดของ tier เริ่มต้นได้ ถ้า min_points ยังเป็น 0
        $this->put(route('admin.tiers.update', $base), [
            'tier_name' => 'Starter',
            'min_points' => 0,
            'discount_percent' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('MEMBERSHIP_TIER', ['tier_id' => $base->tier_id, 'tier_name' => 'Starter']);
    }

    public function test_tier_with_members_cannot_be_deleted(): void
    {
        $this->actingAs($this->makeUser('admin'));

        $gold = MembershipTier::create(['tier_name' => 'Gold', 'min_points' => 2000, 'discount_percent' => 5]);
        $member = $this->makeUser('member');
        $member->forceFill(['tier_id' => $gold->tier_id])->save();

        $this->from(route('admin.tiers.index'))
            ->delete(route('admin.tiers.destroy', $gold))
            ->assertRedirect(route('admin.tiers.index'))
            ->assertSessionHasErrors('tier');

        $this->assertDatabaseHas('MEMBERSHIP_TIER', ['tier_id' => $gold->tier_id]);
    }
}
