<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class AnalyticsController extends Controller
{
    public function index()
{
    if (\Schema::hasTable('requests')) {
        $requestsPerOffice = DB::table('requests')
            ->select('office_id', DB::raw('count(*) as total'))
            ->groupBy('office_id')
            ->get();
    } else {
        $requestsPerOffice = collect();
    }

    if (\Schema::hasTable('payments')) {
        $revenue = DB::table('payments')->sum('amount');
    } else {
        $revenue = 0;
    }

    return view('admin.analytics', compact('requestsPerOffice','revenue'));
}
}
