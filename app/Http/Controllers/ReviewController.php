<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

// สมาชิก: เขียน แก้ไข ลบรีวิวของตัวเอง
class ReviewController extends Controller
{
    public function store(Request $request, Car $car): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($request, $car, $data) {
            // ล็อกแถวสมาชิก กันกดส่งซ้ำพร้อมกันจนรีวิวเกินจำนวนที่ซื้อ
            $member = User::whereKey($request->user()->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (Review::remainingFor($member, $car) < 1) {
                throw ValidationException::withMessages([
                    'review' => 'You can review this car only after an order containing it is completed, '
                        .'and only once per completed order.',
                ]);
            }

            $review = new Review($data);
            // ผูกกับสมาชิกและรถจากฝั่ง server ไม่รับจากฟอร์ม
            $review->member_id = $member->member_id;
            $review->car_id = $car->car_id;
            $review->save();
        });

        return redirect()->to(route('products.show', $car).'#reviews')
            ->with('success', 'Thank you! Your review has been posted.');
    }

    public function edit(Request $request, Review $review): View
    {
        $this->ensureOwner($request, $review);

        $review->load('car');

        return view('reviews.edit', compact('review'));
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        $this->ensureOwner($request, $review);

        $review->update($this->validated($request));

        return redirect()->to(route('products.show', $review->car_id).'#reviews')
            ->with('success', 'Your review has been updated.');
    }

    public function destroy(Request $request, Review $review): RedirectResponse
    {
        $this->ensureOwner($request, $review);

        $carId = $review->car_id;
        $review->delete();

        return redirect()->to(route('products.show', $carId).'#reviews')
            ->with('success', 'Your review has been deleted.');
    }

    private function ensureOwner(Request $request, Review $review): void
    {
        abort_unless(
            (int) $review->member_id === (int) $request->user()->getKey(),
            403,
            'You can only change your own reviews.',
        );
    }

    private function validated(Request $request): array
    {
        return $request->validate(
            [
                'rating' => ['required', 'integer', 'between:1,5'],
                'comment' => ['required', 'string', 'max:2000'],
            ],
            [],
            [
                'rating' => 'rating',
                'comment' => 'review',
            ],
        );
    }
}
