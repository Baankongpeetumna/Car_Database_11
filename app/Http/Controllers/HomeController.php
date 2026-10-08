<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Car;
use App\Models\Category;
use App\Models\MembershipTier;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $brands = Brand::withCount('cars')
            ->orderByDesc('cars_count')
            ->orderBy('brand_name')
            ->get();

        $categories = Category::withCount('cars')
            ->orderBy('category_name')
            ->get();

        // รถมาใหม่: เพิ่มเข้าระบบล่าสุดและยังมีของ
        $latestCars = Car::with(['brand', 'category'])
            ->where('stock_qty', '>', 0)
            ->orderByDesc('car_id')
            ->limit(4)
            ->get();

        $priceSteps = [500000, 1000000, 1500000, 2000000, 3000000];

        return view('home', [
            'brands' => $brands,
            'categories' => $categories,
            'latestCars' => $latestCars,
            'tiers' => MembershipTier::orderBy('min_points')->get(),
            'totalCars' => Car::where('stock_qty', '>', 0)->count(),
            'priceSteps' => $priceSteps,
            'heroCar' => $latestCars->first(),
        ]);
    }
}
