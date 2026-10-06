<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Cart;
use App\Models\User;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $request->user()->cart()->first();

        $cars = $cart
            ? $cart->cars()->with(['brand', 'category'])->get()
            : collect();

        $items = $cars->map(function (Car $car): array {
            $quantity = (int) $car->pivot->quantity;
            $price = Money::cents($car->price);

            return [
                'car' => $car,
                'quantity' => $quantity,
                'unit_price_cents' => $price,
                'line_total_cents' => $price * $quantity,
            ];
        });

        return view('cart.index', [
            'items' => $items,
            'totalQuantity' => $items->sum('quantity'),
            'subtotalCents' => $items->sum('line_total_cents'),
            'canCheckout' => $items->isNotEmpty()
                && $items->every(
                    fn ($item) => $item['quantity'] > 0
                        && $item['quantity'] <= (int) $item['car']->stock_qty
                ),
        ]);
    }

    public function store(Request $request, Car $car): RedirectResponse
    {
        $input = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);

        DB::transaction(function () use ($request, $car, $input) {
            $cart = $this->lockedCart($request);

            $car = Car::whereKey($car->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $existing = $cart->cars()
                ->where('CAR.car_id', $car->car_id)
                ->first();

            $existingQuantity = $existing
                ? (int) $existing->pivot->quantity
                : 0;

            // ตรวจจำนวนเดิม + จำนวนที่เพิ่ม
            $quantity = $existingQuantity + (int) $input['quantity'];

            $this->checkStock($car, $quantity);

            if ($existing) {
                $cart->cars()->updateExistingPivot(
                    $car->car_id,
                    ['quantity' => $quantity]
                );
            } else {
                $cart->cars()->attach(
                    $car->car_id,
                    ['quantity' => $quantity]
                );
            }
        }, 3);

        return redirect()->route('cart.index')
            ->with('success', 'เพิ่มรถลงตะกร้าแล้ว');
    }

    public function update(Request $request, Car $car): RedirectResponse
    {
        $input = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);

        DB::transaction(function () use ($request, $car, $input) {
            $cart = $this->lockedCart($request);

            $car = Car::whereKey($car->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                $cart->cars()
                    ->where('CAR.car_id', $car->car_id)
                    ->exists(),
                404
            );

            $quantity = (int) $input['quantity'];

            $this->checkStock($car, $quantity);

            $cart->cars()->updateExistingPivot(
                $car->car_id,
                ['quantity' => $quantity]
            );
        }, 3);

        return redirect()->route('cart.index')
            ->with('success', 'อัปเดตจำนวนแล้ว');
    }

    public function destroy(Request $request, Car $car): RedirectResponse
    {
        DB::transaction(function () use ($request, $car) {
            $cart = $this->lockedCart($request);

            abort_if(
                $cart->cars()->detach($car->car_id) === 0,
                404
            );
        }, 3);

        return redirect()->route('cart.index')
            ->with('success', 'ลบรายการแล้ว');
    }

    private function lockedCart(Request $request): Cart
    {
        $member = User::whereKey($request->user()->getKey())
            ->lockForUpdate()
            ->firstOrFail();

        return $member->cart()->firstOrCreate([]);
    }

    private function checkStock(Car $car, int $quantity): void
    {
        if ($quantity > (int) $car->stock_qty) {
            throw ValidationException::withMessages([
                'quantity' => "{$car->model_name} เหลือ {$car->stock_qty} คัน "
                    .'จำนวนรวมในตะกร้าต้องไม่เกินสต็อก',
            ]);
        }
    }
}