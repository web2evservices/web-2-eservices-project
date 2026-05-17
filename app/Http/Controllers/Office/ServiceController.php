<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Government_Offices;
use App\Models\Services;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    private function getOffice()
    {
        $userOffice = Auth::user()->office;

        if (!$userOffice) {
            return null;
        }

        $governmentOffice = Government_Offices::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'name' => $userOffice->name,
                'address' => $userOffice->address,
                'municipality_id' => $userOffice->municipality_id,
                'contact_info' => $userOffice->contact_info ?? '',
                'latitude' => $userOffice->latitude ?? 0,
                'longitude' => $userOffice->longitude ?? 0,
            ]
        );

        $governmentOffice->update([
            'name' => $userOffice->name,
            'address' => $userOffice->address,
            'municipality_id' => $userOffice->municipality_id,
            'contact_info' => $userOffice->contact_info ?? '',
            'latitude' => $userOffice->latitude ?? 0,
            'longitude' => $userOffice->longitude ?? 0,
        ]);

        return $governmentOffice;
    }

    public function index()
    {
        $office = $this->getOffice();

        if (!$office) {
            return redirect()->route('office.profile.edit')
                ->with('error', 'Please create your office profile before managing services.');
        }

        $services = Services::where('office_id', $office->id)
            ->with('category')
            ->get();

        return view('office.services.index', compact('services', 'office'));
    }

    public function create()
    {
        $office = $this->getOffice();
        if (!$office) {
            return redirect()->route('office.profile.edit')
                ->with('error', 'Please create your office profile before adding services.');
        }

        $categories = ServiceCategory::all();
        return view('office.services.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                   => 'required|string|max:255',
            'category_id'            => 'required|exists:service_categories,id',
            'price'                  => 'required|numeric|min:0|max:99999999.99',
            'duration'               => 'required|integer|min:1',
            'required_documents'     => 'nullable|array',
            'required_documents.*'   => 'string|max:255',
        ]);

        $office = $this->getOffice();

        if (!$office) {
            return redirect()->route('office.profile.edit')
                ->with('error', 'Please create your office profile before adding services.');
        }

        $service = Services::create([
            'office_id'          => $office->id,
            'name'               => $request->name,
            'category_id'        => $request->category_id,
            'price'              => $request->price,
            'duration'           => $request->duration,
            'required_documents' => $request->required_documents ?? [],
        ]);

        ActivityLogger::created(
            'service',
            $service->id,
            "Created service \"{$service->name}\"",
            $service->only(['name', 'price', 'duration', 'category_id'])
        );

        return redirect()->route('office.services.index')
            ->with('success', 'Service created successfully.');
    }

    public function edit($id)
    {
        $service = Services::findOrFail($id);
        $this->authorizeOffice($service);
        $categories = ServiceCategory::all();
        return view('office.services.edit', compact('service', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $service = Services::findOrFail($id);
        $this->authorizeOffice($service);

        $request->validate([
            'name'                   => 'required|string|max:255',
            'category_id'            => 'required|exists:service_categories,id',
            'price'                  => 'required|numeric|min:0|max:99999999.99',
            'duration'               => 'required|integer|min:1',
            'required_documents'     => 'nullable|array',
            'required_documents.*'   => 'string|max:255',
        ]);

        $old = $service->only(['name', 'category_id', 'price', 'duration', 'required_documents']);

        $service->update([
            'name'               => $request->name,
            'category_id'        => $request->category_id,
            'price'              => $request->price,
            'duration'           => $request->duration,
            'required_documents' => $request->required_documents ?? [],
        ]);

        ActivityLogger::updated(
            'service',
            $service->id,
            "Updated service \"{$service->name}\"",
            $old,
            $service->only(['name', 'category_id', 'price', 'duration', 'required_documents'])
        );

        return redirect()->route('office.services.index')
            ->with('success', 'Service updated successfully.');
    }

    public function destroy($id)
    {
        $service = Services::findOrFail($id);
        $this->authorizeOffice($service);

        ActivityLogger::deleted(
            'service',
            $service->id,
            "Deleted service \"{$service->name}\"",
            $service->only(['name', 'price', 'duration', 'category_id'])
        );

        $service->delete();

        return redirect()->route('office.services.index')
            ->with('success', 'Service deleted.');
    }

    // Prevent an office user from editing another office's services
    private function authorizeOffice(Services $service)
    {
        $office = $this->getOffice();

        if (!$office || $service->office_id !== $office->id) {
            abort(403, 'This service does not belong to your office.');
        }
    }
}