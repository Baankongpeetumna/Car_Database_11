<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Brand;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $brands = Brand::withCount('cars')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('brand_name', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%");
            })
            ->orderBy('brand_id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.brands.index', compact('brands'));
    }

    public function create(): View
    {
        return view('admin.brands.create', ['brand' => new Brand()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $brand = Brand::create($this->validated($request));

        AdminLog::record('created', $brand, $brand->brand_name, AdminLog::snapshot($brand));

        return redirect()->route('admin.brands.index')
            ->with('success', "Brand {$brand->brand_name} added.");
    }

    public function edit(Brand $brand): View
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $brand->fill($this->validated($request, $brand));
        $changes = AdminLog::pendingChanges($brand);
        $brand->save();

        // บันทึก log เฉพาะเมื่อมีค่าเปลี่ยนจริง
        if ($changes !== []) {
            AdminLog::record('updated', $brand, $brand->brand_name, $changes);
        }

        return redirect()->route('admin.brands.index')
            ->with('success', "Brand {$brand->brand_name} updated.");
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        // FK ของ CAR ไม่มี onDelete จึงต้องเช็กก่อนลบ
        $carCount = $brand->cars()->count();

        if ($carCount > 0) {
            return back()->withErrors([
                'brand' => "Cannot delete brand {$brand->brand_name} "
                    ."because {$carCount} ".Str::plural('car', $carCount).' still use it.',
            ]);
        }

        try {
            $brand->delete();
        } catch (QueryException) {
            // กันกรณีมีรถถูกเพิ่มเข้ามาระหว่างเช็กกับลบ
            return back()->withErrors([
                'brand' => "Cannot delete brand {$brand->brand_name} "
                    .'because other records still reference it.',
            ]);
        }

        AdminLog::record('deleted', $brand, $brand->brand_name, AdminLog::snapshot($brand, deleted: true));

        return redirect()->route('admin.brands.index')
            ->with('success', "Brand {$brand->brand_name} deleted.");
    }

    private function validated(Request $request, ?Brand $brand = null): array
    {
        return $request->validate(
            [
                'brand_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('BRAND', 'brand_name')
                        ->ignore($brand?->brand_id, 'brand_id'),
                ],
                'country' => ['required', 'string', 'max:100'],
            ],
            [
                'brand_name.unique' => 'This brand already exists.',
            ],
            [
                'brand_name' => 'brand name',
                'country' => 'country',
            ],
        );
    }
}
