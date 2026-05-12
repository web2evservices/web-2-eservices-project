<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Services;
use App\Models\ServiceRequests;
use App\Models\Office;
use Illuminate\Support\Facades\Auth;

class OfficeDashboardController extends Controller
{
    public function index()
    {
        $office = Office::where('user_id', Auth::id())->first();

        if (!$office) {
            return view('office.dashboard', [
                'office'          => null,
                'totalServices'   => 0,
                'pendingRequests' => 0,
                'recentRequests'  => collect(),
            ]);
        }

        $totalServices = Services::where('office_id', $office->id)->count();

        $pendingRequests = ServiceRequests::whereHas('service', function ($q) use ($office) {
            $q->where('office_id', $office->id);
        })->where('status', 'Pending')->count();

        $recentRequests = ServiceRequests::whereHas('service', function ($q) use ($office) {
            $q->where('office_id', $office->id);
        })->with(['service', 'citizen'])->latest()->take(5)->get();

        return view('office.dashboard', compact(
            'office',
            'totalServices',
            'pendingRequests',
            'recentRequests'
        ));
    }
}