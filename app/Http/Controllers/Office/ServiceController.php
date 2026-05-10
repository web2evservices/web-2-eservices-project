<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Services;
use App\Models\Service_Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    private function getOffice()
    {
        return Auth::user()->office;
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

        $categories = Service_Categories::all();
        return view('office.services.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                   => 'required|string|max:255',
            'category_id'            => 'required|exists:service__categories,id',
            'price'                  => 'required|numeric|min:0',
            'duration'               => 'required|integer|min:1',
            'required_documents'     => 'nullable|array',
            'required_documents.*'   => 'string|max:255',
        ]);

        $office = $this->getOffice();

        if (!$office) {
            return redirect()->route('office.profile.edit')
                ->with('error', 'Please create your office profile before adding services.');
        }

        Services::create([
            'office_id'          => $office->id,
            'name'               => $request->name,
            'category_id'        => $request->category_id,
            'price'              => $request->price,
            'duration'           => $request->duration,
            'required_documents' => $request->required_documents ?? [],
        ]);

        return redirect()->route('office.services.index')
            ->with('success', 'Service created successfully.');
    }

    public function edit($id)
    {
        $service = Services::findOrFail($id);
        $this->authorizeOffice($service);
        $categories = Service_Categories::all();
        return view('office.services.edit', compact('service', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $service = Services::findOrFail($id);
        $this->authorizeOffice($service);

        $request->validate([
            'name'                   => 'required|string|max:255',
            'category_id'            => 'required|exists:service__categories,id',
            'price'                  => 'required|numeric|min:0',
            'duration'               => 'required|integer|min:1',
            'required_documents'     => 'nullable|array',
            'required_documents.*'   => 'string|max:255',
        ]);

        $service->update([
            'name'               => $request->name,
            'category_id'        => $request->category_id,
            'price'              => $request->price,
            'duration'           => $request->duration,
            'required_documents' => $request->required_documents ?? [],
        ]);

        return redirect()->route('office.services.index')
            ->with('success', 'Service updated successfully.');
    }

    public function destroy($id)
    {
        $service = Services::findOrFail($id);
        $this->authorizeOffice($service);
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