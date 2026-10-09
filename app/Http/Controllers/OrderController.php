<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()->orders()
            ->orderByDesc('order_id')
            ->paginate(10);

        // ออเดอร์ completed ที่ยังมีรถให้รีวิว (แสดงป้าย Review available)
        $reviewable = $this->reviewsLeftByCar(
            $request,
            $orders->getCollection()
                ->where('status', Review::ELIGIBLE_ORDER_STATUS)
                ->load('cars')
                ->flatMap->cars
        );

        $reviewableOrderIds = $orders->getCollection()
            ->filter(fn (Order $order) => $order->status === Review::ELIGIBLE_ORDER_STATUS
                && $order->cars->contains(fn ($car) => ($reviewable[$car->car_id] ?? 0) > 0))
            ->pluck('order_id')
            ->all();

        return view('orders.index', compact('orders', 'reviewableOrderIds'));
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless(
            (int) $order->member_id
                === (int) $request->user()->getKey(),
            404
        );

        $order->load(['cars.brand']);

        // รถในออเดอร์ completed ที่ยังรีวิวได้อีกกี่ครั้ง [car_id => จำนวน]
        $reviewsLeft = $order->status === Review::ELIGIBLE_ORDER_STATUS
            ? $this->reviewsLeftByCar($request, $order->cars)
            : [];

        return view('orders.show', compact('order', 'reviewsLeft'));
    }

    private function reviewsLeftByCar(Request $request, iterable $cars): array
    {
        $left = [];

        foreach ($cars as $car) {
            $left[$car->car_id] ??= Review::remainingFor($request->user(), $car);
        }

        return $left;
    }
}
