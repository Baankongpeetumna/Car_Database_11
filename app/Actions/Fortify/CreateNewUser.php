<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input): User
    {
        $input['email'] = strtolower(
            trim((string) ($input['email'] ?? ''))
        );

        $validated = Validator::make($input, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('MEMBER', 'email'),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:5000'],
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($validated): User {
            $tier = DB::table('MEMBERSHIP_TIER')
                ->where('min_points', 0)
                ->orderBy('tier_id')
                ->first();

            if ($tier === null) {
                throw ValidationException::withMessages([
                    'email' => 'ยังไม่มีระดับสมาชิกเริ่มต้น กรุณาให้ผู้ดูแลรัน MembershipTierSeeder ก่อน',
                ]);
            }

            $user = new User();

            $user->fill([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);

            // สิทธิ์และคะแนนกำหนดจากฝั่ง Server เท่านั้น
            $user->forceFill([
                'role' => 'member',
                'points' => 0,
                'tier_id' => $tier->tier_id,
            ]);

            $user->save();

            return $user;
        });
    }
}