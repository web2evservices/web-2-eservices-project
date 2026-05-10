<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfficeProfileController extends Controller
{
    public function edit()
    {
        $office = Auth::user()->office;
        return view('office.profile.edit', compact('office'));
    }

    public function update(Request $request)
    {
        $office = Auth::user()->office;

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'address'       => 'required|string|max:500',
            'working_hours' => 'required|string|max:255',
            'contact_info'  => 'required|string|max:255',
            'latitude'      => 'nullable|numeric|between:-90,90',
            'longitude'     => 'nullable|numeric|between:-180,180',
        ]);

        if (!$office) {
            return redirect()->route('office.profile.edit')
                ->withInput()
                ->with('error', 'No office is linked to your account yet. Please ask admin to create your office or assign an existing office to you.');
        }

        $office->update($validated);

        return redirect()->route('office.profile.edit')
            ->with('success', 'Office profile updated successfully.');
    }
}