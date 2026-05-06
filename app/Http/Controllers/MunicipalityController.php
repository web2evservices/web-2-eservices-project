<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Municipality;

class MunicipalityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $municipalities = Municipality::latest()->paginate(10);
        return view('admin.municipalities.index', compact('municipalities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.municipalities.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Municipality::create($request->validate([
            'name'=>'required',
            'city'=>'required'
        ]));

         return redirect('/admin/municipalities')->with('success', 'Municipality created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Municipality $municipality)
    {
        return view('admin.municipalities.edit', compact('municipality'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Municipality $municipality)
    {
       $request->validate([
        'name' => 'required',
        'city' => 'required',
     ]);

    $municipality->update([
        'name' => $request->name,
        'city' => $request->city,
    ]);

    return redirect('/admin/municipalities')->with('success', 'Updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Municipality $municipality)
    {
        $municipality->delete();
        return back()->with('success','Deleted');
    }
}
