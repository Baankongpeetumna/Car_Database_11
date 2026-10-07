<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

// Admin ดูประวัติว่าใครแก้อะไร (อ่านอย่างเดียว ไม่มีแก้/ลบ log)
class AdminLogController extends Controller
{
    // ตารางที่มีการบันทึก log [ชื่อตาราง => ชื่อที่แสดง]
    private const SUBJECTS = [
        'CAR' => 'Cars',
        'BRAND' => 'Brands',
        'CATEGORY' => 'Categories',
        'MEMBERSHIP_TIER' => 'Membership Tiers',
        'REVIEW' => 'Reviews',
        'ORDERS' => 'Orders',
        'MEMBER' => 'Members',
    ];

    private const ACTIONS = ['created', 'updated', 'deleted', 'restocked', 'status_changed', 'role_changed'];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'subject' => ['nullable', Rule::in(array_keys(self::SUBJECTS))],
            'action' => ['nullable', Rule::in(self::ACTIONS)],
            'admin' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $search = trim((string) ($filters['q'] ?? ''));

        $logs = AdminLog::with('member')
            ->when($filters['subject'] ?? null, fn ($q, $subject) => $q->where('subject_type', $subject))
            ->when($filters['action'] ?? null, fn ($q, $action) => $q->where('action', $action))
            ->when($filters['admin'] ?? null, fn ($q, $id) => $q->where('member_id', $id))
            ->when($search !== '', fn ($q) => $q->where('description', 'like', "%{$search}%"))
            ->orderByDesc('log_id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.logs.index', [
            'logs' => $logs,
            'subjects' => self::SUBJECTS,
            'actions' => self::ACTIONS,
            'admins' => User::where('role', 'admin')->orderBy('first_name')->get(),
        ]);
    }
}
