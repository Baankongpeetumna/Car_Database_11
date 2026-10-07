<?php

namespace App\Console\Commands;

use App\Models\AdminLog;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

// ตั้ง/ถอดยศ admin จาก terminal (ใช้ตั้ง admin คนแรก หรือกู้คืนเมื่อไม่มีใครเข้าหลังร้านได้)
// ตัวอย่าง: php artisan member:role somchai@mail.com admin
class MemberRoleCommand extends Command
{
    private const ROLES = ['admin', 'member'];

    protected $signature = 'member:role
                            {email : Email of the member}
                            {role : admin or member}';

    protected $description = 'Make a member an admin, or turn an admin back into a member';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        $role = strtolower(trim((string) $this->argument('role')));

        if (! in_array($role, self::ROLES, true)) {
            $this->error("Role must be one of: ".implode(', ', self::ROLES).'.');

            return self::FAILURE;
        }

        $result = DB::transaction(function () use ($email, $role) {
            // ล็อกแถว admin ทั้งหมด กันถอดยศพร้อมกันจนไม่เหลือ admin
            $adminCount = User::where('role', 'admin')->lockForUpdate()->count();

            $user = User::where('email', $email)->lockForUpdate()->first();

            if (! $user) {
                return ['error', "No member found with email {$email}."];
            }

            $oldRole = $user->role;

            if ($oldRole === $role) {
                return ['info', "{$user->name} ({$user->email}) is already {$this->label($role)}. Nothing changed."];
            }

            if ($oldRole === 'admin' && $adminCount <= 1) {
                return ['error', 'Cannot remove the last admin. Make someone else an admin first.'];
            }

            // role ไม่อยู่ใน fillable จึงต้องใช้ forceFill (ตั้งจากฝั่ง server เท่านั้น)
            $user->forceFill(['role' => $role])->save();

            AdminLog::record('role_changed', $user, "{$user->name} ({$user->email})", [
                'role' => [$oldRole, $role],
            ]);

            return ['success', "{$user->name} ({$user->email}) is now {$this->label($role)}."];
        });

        [$type, $message] = $result;

        if ($type === 'error') {
            $this->error($message);
        } else {
            $this->info($message);
        }

        return $type === 'error' ? self::FAILURE : self::SUCCESS;
    }

    private function label(string $role): string
    {
        return $role === 'admin' ? 'an admin' : 'a member';
    }
}
