<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Office;
use App\Models\Municipality;
use App\Models\User;

class OfficeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $offices = Office::with('municipality','user')->paginate(10);
        return view('admin.offices.index', compact('offices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $municipalities = Municipality::pluck('name','id');
        $officeUsers = User::where('role', 'office_user')->get();
        return view('admin.offices.create', compact('municipalities', 'officeUsers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       $validated = $request->validate([
            'name'=>'required',
            'email'=>'required|email',
            'municipality_id'=>'required',
            'address'=>'required',
            'user_id' => 'nullable|exists:users,id'
        ]);

        if (!empty($validated['user_id'])) {
        $user = User::findOrFail($validated['user_id']);

        if ($user->role !== 'office_user') {
            return back()->withErrors([
                'user_id' => 'Selected user is not an office user.'
            ]);
        }
        }
        Office::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'municipality_id' => $validated['municipality_id'],
        'address' => $validated['address'],
        'user_id' => $validated['user_id'] ?? null,
    ]);

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
        $officeUsers = User::where('role', 'office_user')->get();
        $municipalities = Municipality::all();
        return view('admin.offices.edit', compact('office', 'municipalities', 'officeUsers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Office $office)
    {
        $validated = $request->validate([
        'name' => 'required',
        'email' => 'required|email',
        'address' => 'required',
        'municipality_id' => 'required',
        'user_id' => 'nullable|exists:users,id'
    ]);
        if (!empty($validated['user_id'])) {
        $user = User::findOrFail($validated['user_id']);

        if ($user->role !== 'office_user') {
            return back()->withErrors([
                'user_id' => 'Selected user is not an office user.'
            ]);
        }
        }

        $office->update([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'address' => $validated['address'],
        'municipality_id' => $validated['municipality_id'],
        'user_id' => $validated['user_id'] ?? null,
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
