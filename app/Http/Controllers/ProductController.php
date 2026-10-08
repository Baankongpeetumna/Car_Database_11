<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Car;
use App\Models\Category;
use App\Models\Review;
use App\Support\CarVisual;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /** ตัวเลือกการเรียงลำดับ: key => [label, column, direction] (รองรับ price_asc / price_desc เดิม) */
    public const SORTS = [
        'latest' => ['Newest arrivals', 'car_id', 'desc'],
        'year_desc' => ['Model year: newest', 'model_year', 'desc'],
        'price_asc' => ['Price: Low → High', 'price', 'asc'],
        'price_desc' => ['Price: High → Low', 'price', 'desc'],
        'mileage_asc' => ['Lowest mileage', 'mileage_km', 'asc'],
    ];

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

        // รองรับทั้งค่าเดียว (?brand=1) และหลายค่า (?brand[]=1&brand[]=2)
        $brandIds = $this->ids($request, 'brand');
        if ($brandIds !== []) {
            $query->whereIn('brand_id', $brandIds);
        }

        $categoryIds = $this->ids($request, 'category');
        if ($categoryIds !== []) {
            $query->whereIn('category_id', $categoryIds);
        }

        $fuels = $this->strings($request, 'fuel');
        if ($fuels !== []) {
            $query->whereIn('fuel_type', $fuels);
        }

        $transmissions = $this->strings($request, 'transmission');
        if ($transmissions !== []) {
            $query->whereIn('transmission', $transmissions);
        }

        $condition = $request->query('condition');
        if (in_array($condition, ['new', 'used'], true)) {
            $this->whereCondition($query, $condition);
        }

        foreach ([
            'min_price' => ['price', '>='],
            'max_price' => ['price', '<='],
            'year_from' => ['model_year', '>='],
            'year_to' => ['model_year', '<='],
            'max_mileage' => ['mileage_km', '<='],
        ] as $param => [$column, $operator]) {
            $value = $request->query($param);
            if (is_numeric($value) && $value >= 0) {
                $query->where($column, $operator, $value);
            }
        }

        if ($request->boolean('in_stock')) {
            $query->where('stock_qty', '>', 0);
        }

        $sort = array_key_exists((string) $request->query('sort'), self::SORTS)
            ? (string) $request->query('sort')
            : 'latest';
        [, $column, $direction] = self::SORTS[$sort];
        $query->orderBy($column, $direction)->orderByDesc('car_id');

        return view('products.index', [
            'title' => 'Cars',
            'cars' => $query->paginate(9)->withQueryString(),
            'brands' => Brand::withCount('cars')->orderBy('brand_name')->get(),
            'categories' => Category::withCount('cars')->orderBy('category_name')->get(),
            'fuelOptions' => Car::query()->distinct()->orderBy('fuel_type')->pluck('fuel_type')->filter()->values(),
            'transmissionOptions' => Car::query()->distinct()->orderBy('transmission')->pluck('transmission')->filter()->values(),
            'yearRange' => [(int) Car::min('model_year'), (int) Car::max('model_year')],
            'sorts' => self::SORTS,
            'sort' => $sort,
            'condition' => in_array($condition, ['new', 'used'], true) ? $condition : null,
            'selected' => [
                'brand' => $brandIds,
                'category' => $categoryIds,
                'fuel' => $fuels,
                'transmission' => $transmissions,
            ],
            'conditionCounts' => [
                'all' => Car::count(),
                'new' => $this->whereCondition(Car::query(), 'new')->count(),
                'used' => $this->whereCondition(Car::query(), 'used')->count(),
            ],
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

        // รถที่คล้ายกัน: ยี่ห้อเดียวกันหรือประเภทเดียวกัน (ยี่ห้อเดียวกันขึ้นก่อน)
        $similar = Car::with(['brand', 'category'])
            ->whereKeyNot($car->getKey())
            ->where(fn ($q) => $q->where('brand_id', $car->brand_id)->orWhere('category_id', $car->category_id))
            ->orderByRaw('CASE WHEN brand_id = ? THEN 0 ELSE 1 END', [$car->brand_id])
            ->orderByDesc('car_id')
            ->limit(4)
            ->get();

        return view('products.show', [
            'title' => $car->model_name,
            'car' => $car,
            'reviews' => $reviews,
            'averageRating' => $rated->isNotEmpty() ? round($rated->avg('rating'), 1) : null,
            'ratedCount' => $rated->count(),
            'reviewsLeft' => $reviewsLeft,
            'similar' => $similar,
        ]);
    }
    /** กรองรถใหม่ / มือสอง จากค่า car_condition */
    private function whereCondition(Builder $query, string $condition): Builder
    {
        $placeholders = implode(',', array_fill(0, count(CarVisual::NEW_VALUES), '?'));
        $sql = "LOWER(TRIM(car_condition)) IN ({$placeholders})";

        return $condition === 'new'
            ? $query->whereRaw($sql, CarVisual::NEW_VALUES)
            : $query->whereRaw("NOT ({$sql})", CarVisual::NEW_VALUES);
    }

    /** @return list<int> */
    private function ids(Request $request, string $key): array
    {
        return collect((array) $request->query($key, []))
            ->filter(fn ($v) => is_numeric($v) && (int) $v > 0)
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->values()
            ->all();
    }

    /** @return list<string> */
    private function strings(Request $request, string $key): array
    {
        return collect((array) $request->query($key, []))
            ->filter(fn ($v) => is_string($v) && trim($v) !== '' && mb_strlen($v) <= 50)
            ->map(fn ($v) => trim($v))
            ->unique()
            ->values()
            ->all();
    }
}
