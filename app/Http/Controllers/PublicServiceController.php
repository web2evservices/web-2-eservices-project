<?php

namespace App\Http\Controllers;

use App\Models\Services;
use App\Models\Government_Offices;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class PublicServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Services::with(['office', 'category']);

        // Filter by office
        if ($request->filled('office_id')) {
            $query->where('office_id', $request->office_id);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $services = $query->paginate(12);

        $offices = Government_Offices::all();
        $categories = ServiceCategory::all();

        return view('services.index', compact('services', 'offices', 'categories'));
    }

    public function show($id)
    {
        $service = Services::with(['office', 'category'])->findOrFail($id);

        return view('services.show', compact('service'));
    }

    public function apiShow($id)
    {
        $service = Services::with(['office', 'category'])->findOrFail($id);

        return response()->json(['data' => $service]);
    }
}