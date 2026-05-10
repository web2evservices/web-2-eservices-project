<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Service_Categories;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function index()
    {
        $categories = Service_Categories::withCount('services')->get();
        return view('office.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('office.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:service__categories,name',
            'description' => 'nullable|string',
        ]);

        Service_Categories::create($request->only('name', 'description'));

        return redirect()->route('office.categories.index')
            ->with('success', 'Category created successfully.');
    }

    
public function edit($id)
{
    $category = Service_Categories::findOrFail($id);
    return view('office.categories.edit', compact('category'));
}

public function update(Request $request, $id)
{
    $category = Service_Categories::findOrFail($id);

    $request->validate([
        'name'        => 'required|string|max:255|unique:service__categories,name,' . $id,
        'description' => 'nullable|string',
    ]);

    $category->update($request->only('name', 'description'));

    return redirect()->route('office.categories.index')
        ->with('success', 'Category updated successfully.');
}

public function destroy($id)
{
    $category = Service_Categories::findOrFail($id);
    $category->delete();
    return redirect()->route('office.categories.index')
        ->with('success', 'Category deleted.');
}
}