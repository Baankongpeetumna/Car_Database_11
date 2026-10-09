<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $categories = Category::withCount('cars')
            ->when($search !== '', fn ($query) => $query->where('category_name', 'like', "%{$search}%"))
            ->orderBy('category_id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create', ['category' => new Category()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $category = Category::create($this->validated($request));

        AdminLog::record('created', $category, $category->category_name, AdminLog::snapshot($category));

        return redirect()->route('admin.categories.index')
            ->with('success', "Category {$category->category_name} added.");
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->fill($this->validated($request, $category));
        $changes = AdminLog::pendingChanges($category);
        $category->save();

        // บันทึก log เฉพาะเมื่อมีค่าเปลี่ยนจริง
        if ($changes !== []) {
            AdminLog::record('updated', $category, $category->category_name, $changes);
        }

        return redirect()->route('admin.categories.index')
            ->with('success', "Category {$category->category_name} updated.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        // FK ของ CAR ไม่มี onDelete จึงต้องเช็กก่อนลบ
        $carCount = $category->cars()->count();

        if ($carCount > 0) {
            return back()->withErrors([
                'category' => "Cannot delete category {$category->category_name} "
                    ."because {$carCount} ".Str::plural('car', $carCount).' still use it.',
            ]);
        }

        try {
            $category->delete();
        } catch (QueryException) {
            // กันกรณีมีรถถูกเพิ่มเข้ามาระหว่างเช็กกับลบ
            return back()->withErrors([
                'category' => "Cannot delete category {$category->category_name} "
                    .'because other records still reference it.',
            ]);
        }

        AdminLog::record('deleted', $category, $category->category_name, AdminLog::snapshot($category, deleted: true));

        return redirect()->route('admin.categories.index')
            ->with('success', "Category {$category->category_name} deleted.");
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        return $request->validate(
            [
                'category_name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('CATEGORY', 'category_name')
                        ->ignore($category?->category_id, 'category_id'),
                ],
            ],
            [
                'category_name.unique' => 'This category already exists.',
            ],
            [
                'category_name' => 'category name',
            ],
        );
    }
}
