<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Admin: ดูรายการรีวิว และลบรีวิวที่ไม่เหมาะสม
class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
        ]);

        $search = trim((string) ($filters['q'] ?? ''));

        $reviews = Review::with(['member', 'car.brand'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('comment', 'like', "%{$search}%")
                        ->orWhereHas('car', fn ($c) => $c->where('model_name', 'like', "%{$search}%"))
                        ->orWhereHas('member', function ($m) use ($search) {
                            $m->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['rating'] ?? null, fn ($q, $rating) => $q->where('rating', $rating))
            ->orderByDesc('review_id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reviews.index', compact('reviews'));
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->load(['car', 'member']);
        $review->delete();

        // เก็บข้อความรีวิวไว้ใน log เผื่อต้องย้อนดูว่าลบอะไรไป
        AdminLog::record(
            'deleted',
            $review,
            "Review of {$review->car?->model_name} by {$review->member?->name}",
            AdminLog::snapshot($review, deleted: true),
        );

        return back()->with('success', "Review #{$review->review_id} deleted.");
    }
}
