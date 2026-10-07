<?php

namespace Tests\Feature;

use App\Models\AdminLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Admin\CreatesCatalogData;
use Tests\TestCase;

class MemberRoleCommandTest extends TestCase
{
    use CreatesCatalogData;
    use RefreshDatabase;

    public function test_member_can_be_made_admin_and_it_is_logged(): void
    {
        $member = $this->makeUser('member');

        // อีเมลตัวพิมพ์ใหญ่/ช่องว่างก็หาเจอ
        $this->artisan('member:role', ['email' => '  '.strtoupper($member->email), 'role' => 'admin'])
            ->expectsOutputToContain('is now an admin')
            ->assertExitCode(0);

        $this->assertSame('admin', $member->fresh()->role);

        $log = AdminLog::where('action', 'role_changed')->firstOrFail();
        $this->assertNull($log->member_id); // ทำจาก terminal
        $this->assertSame('MEMBER', $log->subject_type);
        $this->assertSame($member->member_id, $log->subject_id);
        $this->assertSame(['role' => ['member', 'admin']], $log->changes);
    }

    public function test_admin_can_be_turned_back_into_member_when_another_admin_exists(): void
    {
        $this->makeUser('admin');
        $second = $this->makeUser('admin');

        $this->artisan('member:role', ['email' => $second->email, 'role' => 'member'])
            ->expectsOutputToContain('is now a member')
            ->assertExitCode(0);

        $this->assertSame('member', $second->fresh()->role);
    }

    public function test_last_admin_cannot_be_removed(): void
    {
        $onlyAdmin = $this->makeUser('admin');

        $this->artisan('member:role', ['email' => $onlyAdmin->email, 'role' => 'member'])
            ->expectsOutputToContain('Cannot remove the last admin')
            ->assertExitCode(1);

        $this->assertSame('admin', $onlyAdmin->fresh()->role);
        $this->assertSame(0, AdminLog::count());
    }

    public function test_unknown_email_or_role_is_rejected(): void
    {
        $member = $this->makeUser('member');

        $this->artisan('member:role', ['email' => 'nobody@example.com', 'role' => 'admin'])
            ->expectsOutputToContain('No member found')
            ->assertExitCode(1);

        $this->artisan('member:role', ['email' => $member->email, 'role' => 'superadmin'])
            ->expectsOutputToContain('Role must be one of')
            ->assertExitCode(1);

        $this->assertSame('member', $member->fresh()->role);
        $this->assertSame(0, AdminLog::count());
    }

    public function test_same_role_changes_nothing(): void
    {
        $member = $this->makeUser('member');

        $this->artisan('member:role', ['email' => $member->email, 'role' => 'member'])
            ->expectsOutputToContain('already a member')
            ->assertExitCode(0);

        $this->assertSame(0, AdminLog::count());
    }

    public function test_role_change_from_terminal_shows_in_activity_log_page(): void
    {
        $member = $this->makeUser('member');
        $this->artisan('member:role', ['email' => $member->email, 'role' => 'admin']);

        $this->actingAs($member->fresh())
            ->get(route('admin.logs.index', ['action' => 'role_changed']))
            ->assertOk()
            ->assertSee('System (terminal)')
            ->assertSee($member->email);
    }
}
