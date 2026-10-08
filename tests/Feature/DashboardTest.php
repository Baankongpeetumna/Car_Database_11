<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// หน้า dashboard ของ starter kit ถูกปิดแล้ว ทุกคนที่เข้า /dashboard จะถูกส่งกลับหน้า Home
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_home_page(): void
    {
        $this->get(route('dashboard'))->assertRedirect('/');
    }

    public function test_authenticated_users_are_redirected_to_the_home_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('dashboard'))->assertRedirect('/');
    }
}
