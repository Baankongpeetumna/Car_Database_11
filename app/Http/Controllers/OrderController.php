<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()->orders()
            ->orderByDesc('order_id')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless(
            (int) $order->member_id
                === (int) $request->user()->getKey(),
            404
        );

        $order->load(['cars.brand']);

        return view('orders.show', compact('order'));
    }
}