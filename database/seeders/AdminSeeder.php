<?php

namespace Database\Seeders;

use App\Models\AdminLog;
use App\Models\MembershipTier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = strtolower(trim(
            (string) config('admin-seed.email')
        ));

        if ($email === '') {
            $this->command?->warn(
                'ข้าม AdminSeeder: กรุณาตั้ง SEED_ADMIN_EMAIL ใน .env'
            );

            return;
        }

        DB::transaction(function () use ($email) {
            $existing = User::where('email', $email)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                if (!$existing->isAdmin()) {
                    throw new RuntimeException(
                        'อีเมลนี้มีบัญชี Member อยู่แล้ว '
                        .'กรุณาเปลี่ยน Role ผ่านหน้าจัดการสมาชิก'
                    );
                }

                $this->command?->info(
                    'มีบัญชี Admin นี้แล้ว ไม่เปลี่ยนข้อมูลเดิม'
                );

                return;
            }

            $input = Validator::make([
                'email' => $email,
                'password' => config('admin-seed.password'),
                'first_name' => config('admin-seed.first_name'),
                'last_name' => config('admin-seed.last_name'),
            ], [
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'string', 'min:12', 'max:72'],
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
            ])->validate();

            $tier = MembershipTier::where('min_points', 0)
                ->orderBy('tier_id')
                ->lockForUpdate()
                ->first();

            if (!$tier) {
                throw new RuntimeException(
                    'กรุณาสร้างระดับสมาชิกที่มี min_points = 0 ก่อน'
                );
            }

            $admin = new User();

            $admin->fill([
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            // User มี password cast เป็น hashed อยู่แล้ว
            $admin->forceFill([
                'role' => 'admin',
                'points' => 0,
                'tier_id' => $tier->tier_id,
            ]);

            $admin->save();

            AdminLog::record(
                'created',
                $admin,
                'Created initial admin account',
                AdminLog::snapshot($admin)
            );

            $this->command?->info('สร้างบัญชี Admin เรียบร้อยแล้ว');
        }, 3);
    }
}