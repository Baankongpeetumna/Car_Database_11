<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Order;
use App\Services\CommerceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'status' => [
                'nullable',
                Rule::in([
                    'pending',
                    'processing',
                    'completed',
                    'cancelled',
                ]),
            ],
        ]);

        $query = Order::with('member')
            ->orderByDesc('order_id');

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['member.tier', 'cars.brand']);

        return view('admin.orders.show', compact('order'));
    }

    public function update(
        Request $request,
        Order $order,
        CommerceService $service
    ): RedirectResponse {
        $input = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    'processing',
                    'completed',
                    'cancelled',
                ]),
            ],
        ]);

        $oldStatus = $order->status;
        $updated = $service->changeStatus($order, $input['status']);

        // บันทึก log (ไม่บันทึกถ้าส่งสถานะเดิมซ้ำ)
        if ($updated->status !== $oldStatus) {
            $changes = ['status' => [$oldStatus, $updated->status]];

            if ($updated->status === 'completed') {
                $changes['points_earned'] = [0, (int) $updated->points_earned];
            }

            AdminLog::record('status_changed', $updated, "Order #{$updated->order_id}", $changes);
        }

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Order status updated.');
    }
}