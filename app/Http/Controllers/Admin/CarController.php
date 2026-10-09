<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Brand;
use App\Models\Car;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CarController extends Controller
{

    private const FUEL_TYPES = ['Petrol', 'Diesel', 'Hybrid', 'Electric'];
    private const TRANSMISSIONS = ['Automatic', 'Manual'];
    private const CONDITIONS = ['New', 'Used'];

    // จำนวน stock สูงสุดต่อรุ่น (ใช้ทั้งฟอร์มรถและปุ่มเติม stock)
    private const MAX_STOCK = 100000;

    // รูปที่อัปโหลดเก็บใน storage/app/public/cars และบันทึกเป็น /storage/cars/xxx.jpg
    private const IMAGE_DIR = 'cars';
    private const IMAGE_URL_PREFIX = '/storage/';

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'integer'],
            'category' => ['nullable', 'integer'],
            'stock' => ['nullable', Rule::in(['in', 'out', 'low'])],        ]);

        $search = trim((string) ($filters['q'] ?? ''));

        $cars = Car::with(['brand', 'category'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('model_name', 'like', "%{$search}%")
                        ->orWhereHas('brand', fn ($b) => $b->where('brand_name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['brand'] ?? null, fn ($q, $id) => $q->where('brand_id', $id))
            ->when($filters['category'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when(($filters['stock'] ?? null) === 'in', fn ($q) => $q->where('stock_qty', '>', 0))
            ->when(($filters['stock'] ?? null) === 'out', fn ($q) => $q->where('stock_qty', 0))
            ->when(($filters['stock'] ?? null) === 'low', fn ($q) => $q->where('stock_qty', '<', 3)->orderBy('stock_qty'))
            ->orderBy('car_id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.cars.index', [
            'cars' => $cars,
            'brands' => Brand::orderBy('brand_name')->get(),
            'categories' => Category::orderBy('category_name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.cars.create', $this->formData(new Car()));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image_url'] = $this->storeImage($request->file('image'));
        }

        $car = Car::create($data);

        AdminLog::record('created', $car, $car->model_name, AdminLog::snapshot($car));

        return redirect()->route('admin.cars.index')
            ->with('success', "Car {$car->model_name} added.");
    }

    public function edit(Car $car): View
    {
        return view('admin.cars.edit', $this->formData($car));
    }

    public function update(Request $request, Car $car): RedirectResponse
    {
        $data = $this->validated($request);
        $oldImage = $car->image_url;

        if ($request->hasFile('image')) {
            $data['image_url'] = $this->storeImage($request->file('image'));
        } elseif ($request->boolean('remove_image')) {
            $data['image_url'] = null;
        }

        $car->fill($data);
        $changes = AdminLog::pendingChanges($car);
        $car->save();

        // บันทึก log เฉพาะเมื่อมีค่าเปลี่ยนจริง
        if ($changes !== []) {
            AdminLog::record('updated', $car, $car->model_name, $changes);
        }

        // ลบไฟล์รูปเก่าหลังบันทึกสำเร็จเท่านั้น
        if (array_key_exists('image_url', $data) && $oldImage !== $data['image_url']) {
            $this->deleteImage($oldImage);
        }

        return redirect()->route('admin.cars.index')
            ->with('success', "Car {$car->model_name} updated.");
    }

    // เติม stock จากหน้ารายการรถ (บวกเพิ่มจากของเดิม)
    public function addStock(Request $request, Car $car): RedirectResponse
    {
        $amount = (int) $request->validate(
            ['amount' => ['required', 'integer', 'min:1', 'max:'.self::MAX_STOCK]],
            [],
            ['amount' => 'stock to add'],
        )['amount'];

        $newStock = DB::transaction(function () use ($car, $amount) {
            // ล็อกแถวรถ กันชนกับ checkout ที่กำลังหักสต็อกพร้อมกัน
            $locked = Car::whereKey($car->getKey())->lockForUpdate()->firstOrFail();
            $oldStock = (int) $locked->stock_qty;
            $newStock = $oldStock + $amount;

            if ($newStock > self::MAX_STOCK) {
                throw ValidationException::withMessages([
                    'amount' => 'Stock cannot exceed '.number_format(self::MAX_STOCK).'.',
                ]);
            }

            $locked->stock_qty = $newStock;
            $locked->save();

            // อยู่ใน transaction เดียวกัน: stock เปลี่ยนเมื่อไร log ต้องมีเสมอ
            AdminLog::record('restocked', $locked, $locked->model_name, [
                'stock_qty' => [$oldStock, $newStock],
            ]);

            return $newStock;
        });

        return back()->with('success', "Added {$amount} to {$car->model_name}. Stock is now {$newStock}.");
    }

    public function destroy(Car $car): RedirectResponse
    {
        // FK จาก ORDER_ITEM, CART_ITEM, REVIEW ไม่มี onDelete จึงต้องเช็กก่อนลบ
        $usage = array_filter([
            'order' => $car->orders()->count(),
            'cart' => DB::table('CART_ITEM')->where('car_id', $car->car_id)->count(),
            'review' => $car->reviews()->count(),
        ]);

        if ($usage !== []) {
            $parts = collect($usage)
                ->map(fn ($count, $label) => $count.' '.Str::plural($label, $count))
                ->implode(', ');

            return back()->withErrors([
                'car' => "Cannot delete {$car->model_name} because it appears in {$parts}. "
                    .'Set stock to 0 to stop selling it instead.',
            ]);
        }

        try {
            $car->delete();
        } catch (QueryException) {
            // กันกรณีมีข้อมูลอ้างอิงเพิ่มเข้ามาระหว่างเช็กกับลบ
            return back()->withErrors([
                'car' => "Cannot delete {$car->model_name} because other records still reference it. "
                    .'Set stock to 0 to stop selling it instead.',
            ]);
        }

        AdminLog::record('deleted', $car, $car->model_name, AdminLog::snapshot($car, deleted: true));

        $this->deleteImage($car->image_url);

        return redirect()->route('admin.cars.index')
            ->with('success', "Car {$car->model_name} deleted.");
    }

    private function formData(Car $car): array
    {
        return [
            'car' => $car,
            'brands' => Brand::orderBy('brand_name')->get(),
            'categories' => Category::orderBy('category_name')->get(),
            'fuelTypes' => self::FUEL_TYPES,
            'transmissions' => self::TRANSMISSIONS,
            'conditions' => self::CONDITIONS,
        ];
    }

    private function validated(Request $request): array
    {
        $data = $request->validate(
            [
                'model_name' => ['required', 'string', 'max:255'],
                'model_year' => ['required', 'integer', 'between:1900,'.(now()->year + 1)],
                'color' => ['required', 'string', 'max:100'],
                'fuel_type' => ['required', Rule::in(self::FUEL_TYPES)],
                'transmission' => ['required', Rule::in(self::TRANSMISSIONS)],
                'car_condition' => ['required', Rule::in(self::CONDITIONS)],
                'engine_cc' => ['required', 'integer', 'min:0', 'max:20000'],
                'mileage_km' => ['required', 'integer', 'min:0', 'max:2000000'],
                // DECIMAL(15,2): จำนวนเต็มไม่เกิน 13 หลัก ทศนิยมไม่เกิน 2 ตำแหน่ง
                'price' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999999.99'],
                'stock_qty' => ['required', 'integer', 'min:0', 'max:'.self::MAX_STOCK],
                'description' => ['nullable', 'string', 'max:5000'],
                'brand_id' => ['required', 'integer', Rule::exists('BRAND', 'brand_id')],
                'category_id' => ['required', 'integer', Rule::exists('CATEGORY', 'category_id')],
                // upload_max_filesize ของ PHP = 2M จึงจำกัดไว้ 2 MB
                'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'remove_image' => ['nullable', 'boolean'],
            ],
            [],
            [
                'model_name' => 'model name',
                'model_year' => 'model year',
                'fuel_type' => 'fuel type',
                'car_condition' => 'condition',
                'engine_cc' => 'engine (cc)',
                'mileage_km' => 'mileage (km)',
                'stock_qty' => 'stock',
                'brand_id' => 'brand',
                'category_id' => 'category',
            ],
        );

        // image / remove_image ไม่ใช่คอลัมน์ในตาราง CAR
        unset($data['image'], $data['remove_image']);

        return $data;
    }

    private function storeImage(UploadedFile $file): string
    {
        return self::IMAGE_URL_PREFIX.$file->store(self::IMAGE_DIR, 'public');
    }

    private function deleteImage(?string $imageUrl): void
    {
        // ลบเฉพาะไฟล์ที่ระบบนี้อัปโหลดเอง (อยู่ใน /storage/cars/)
        $prefix = self::IMAGE_URL_PREFIX.self::IMAGE_DIR.'/';

        if ($imageUrl !== null && str_starts_with($imageUrl, $prefix)) {
            Storage::disk('public')->delete(Str::after($imageUrl, self::IMAGE_URL_PREFIX));
        }
    }
}
