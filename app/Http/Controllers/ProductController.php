<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Car;
use App\Models\Category;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Car::with(['brand', 'category']);

        // ค้นหาจากชื่อรุ่นหรือชื่อยี่ห้อ
        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('model_name', 'like', "%{$search}%")
                    ->orWhereHas('brand', fn ($b) => $b->where('brand_name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('brand')) {
            $query->where('brand_id', $request->integer('brand'));
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->integer('category'));
        }

        if (is_numeric($request->query('min_price'))) {
            $query->where('price', '>=', $request->query('min_price'));
        }

        if (is_numeric($request->query('max_price'))) {
            $query->where('price', '<=', $request->query('max_price'));
        }

        $sort = $request->query('sort');
        if ($sort === 'price_asc') {
            $query->orderBy('price');
        } elseif ($sort === 'price_desc') {
            $query->orderByDesc('price');
        } else {
            $query->orderByDesc('car_id');
        }

        return view('products.index', [
            'title' => 'Cars',
            'cars' => $query->paginate(9)->withQueryString(),
            'brands' => Brand::orderBy('brand_name')->get(),
            'categories' => Category::orderBy('category_name')->get(),
        ]);
    }

    // หน้ารายละเอียดรถ (เปิดดูได้โดยไม่ต้อง login)
    public function show(Request $request, Car $car): View
    {
        $car->load(['brand', 'category']);

        $reviews = $car->reviews()
            ->with('member')
            ->orderByDesc('created_at')
            ->orderByDesc('review_id')
            ->get();

        // ค่าเฉลี่ยนับเฉพาะรีวิวที่มีคะแนน (รีวิวเก่าที่ rating เป็น NULL ไม่นับ)
        $rated = $reviews->whereNotNull('rating');

        // สมาชิกที่ซื้อรถคันนี้แล้ว (ออเดอร์ completed) จะเห็นฟอร์มเขียนรีวิว
        $user = $request->user();
        $reviewsLeft = $user?->isMember() ? Review::remainingFor($user, $car) : 0;

        return view('products.show', [
            'title' => $car->model_name,
            'car' => $car,
            'reviews' => $reviews,
            'averageRating' => $rated->isNotEmpty() ? round($rated->avg('rating'), 1) : null,
            'ratedCount' => $rated->count(),
            'reviewsLeft' => $reviewsLeft,
        ]);
    }
}