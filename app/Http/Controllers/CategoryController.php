<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::latest()->get();
        return view('admin.views.categories.index', compact('categories'));
    }
 // عرض نموذج إنشاء تصنيف
    public function create()
    {
        return view('admin.views.categories.create');
    }

    // حفظ التصنيف الجديد
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Category::create([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'تمت إضافة التصنيف بنجاح');
    }
    public function edit(Category $category)
{
    return view('admin.views.categories.edit', compact('category'));
}

public function update(Request $request, Category $category)
{
    $request->validate([
        'name' => 'required|string|max:255',
    ]);

    $category->update([
        'name' => $request->name,
    ]);

return redirect()->route('admin.categories.index')->with('success', '...');
}
public function destroy(Category $category)
{
    $category->delete();
return redirect()->route('admin.categories.index')->with('success', '...');
}

}
