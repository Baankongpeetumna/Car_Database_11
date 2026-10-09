<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// ประวัติการกระทำของ admin (ใครแก้อะไร เมื่อไร จากค่าอะไรเป็นค่าอะไร)
class AdminLog extends Model
{
    protected $table = 'ADMIN_LOG';
    protected $primaryKey = 'log_id';
    public $timestamps = false;

    protected $fillable = [
        'member_id',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'changes',
    ];
    protected static function booted(): void
    {
        static::creating(function ($log) {
            $log->created_at ??= now();
        });
    }

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id', 'member_id');
    }

    // บันทึก log โดยใช้ admin ที่ login อยู่
    public static function record(string $action, Model $subject, string $description, array $changes = []): self
    {
        return self::create([
            'member_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject->getTable(),
            'subject_id' => $subject->getKey(),
            'description' => mb_substr($description, 0, 255),
            'changes' => $changes === [] ? null : $changes,
        ]);
    }

    // ค่าที่จะเปลี่ยนก่อน save(): ['คอลัมน์' => [ค่าเดิม, ค่าใหม่]] (เรียกหลัง fill() ก่อน save())
    public static function pendingChanges(Model $model): array
    {
        $changes = [];

        foreach ($model->getDirty() as $field => $new) {
            $changes[$field] = [$model->getOriginal($field), $model->getAttribute($field)];
        }

        return $changes;
    }

    // ภาพรวมข้อมูลทั้งแถว ใช้ตอนเพิ่ม (null → ค่า) หรือลบ (ค่า → null)
    public static function snapshot(Model $model, bool $deleted = false): array
    {
        $changes = [];

        foreach ($model->attributesToArray() as $field => $value) {
            if ($field === $model->getKeyName()) {
                continue;
            }

            $changes[$field] = $deleted ? [$value, null] : [null, $value];
        }

        return $changes;
    }
}
