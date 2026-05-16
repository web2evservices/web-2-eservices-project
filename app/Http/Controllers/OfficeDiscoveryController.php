<?php
namespace App\Http\Controllers;

use App\Models\Government_Offices;
use Illuminate\Http\Request;

class OfficeDiscoveryController extends Controller
{
    public function index(Request $request)
    {
        $offices = Government_Offices::with(['services.category', 'municipality'])
            ->get()
            ->filter(fn($o) => $o->latitude && $o->longitude);

        $officesJson = $offices->map(fn($o) => [
            'id'           => $o->id,
            'name'         => $o->name,
            'address'      => $o->address,
            'lat'          => (float) $o->latitude,
            'lng'          => (float) $o->longitude,
            'working_hours'=> $o->working_hours,
            'contact_info' => $o->contact_info,
            'services'     => $o->services->pluck('name'),
        ])->values();

        return view('public.offices.map', [
            'offices'    => $offices,
            'officesJson'=> $officesJson->toJson(),
            'googleMapsKey' => env('GOOGLE_MAPS_API_KEY'),
        ]);
    }

    public function apiOffices()
    {
        $offices = Government_Offices::with('services')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return response()->json(['data' => $offices]);
    }
}