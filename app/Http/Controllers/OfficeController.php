<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Office;
use App\Models\Municipality;

class OfficeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $offices = Office::with('municipality')->paginate(10);
        return view('admin.offices.index', compact('offices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $municipalities = Municipality::pluck('name','id');
        return view('admin.offices.create', compact('municipalities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Office::create($request->validate([
            'name'=>'required',
            'email'=>'required|email',
            'municipality_id'=>'required',
            'address'=>'required'
        ]));

        return redirect()->route('offices.index');
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
    public function edit(Office $office) 
    {
        $municipalities = Municipality::all();
        return view('admin.offices.edit', compact('office', 'municipalities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Office $office)
    {
        $request->validate([
        'name' => 'required',
        'email' => 'required|email',
        'address' => 'required',
        'municipality_id' => 'required',
    ]);

        $office->update([
        'name' => $request->name,
        'email' => $request->email,
        'address' => $request->address,
        'municipality_id' => $request->municipality_id,
    ]);

        return redirect('/admin/offices')->with('success', 'Office updated');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Office $office)
    {
        $office->delete();
        return back()->with('success','Deleted');
    }
}
