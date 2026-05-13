<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnalyticsController extends Controller
{
    public function index()
    {
        $totalRequests = DB::table('service_requests')->count();
        $pendingRequests = DB::table('service_requests')->where('status', 'Pending')->count();
        $completedRequests = DB::table('service_requests')->where('status', 'Completed')->count();
        $requestsByStatus = DB::table('service_requests')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        $totalRevenue = DB::table('payments')->sum('amount');
        $paymentsByMethod = DB::table('payments')
            ->select('payment_method', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->groupBy('payment_method')
            ->get();

        $popularServices = DB::table('service_requests')
            ->join('services', 'service_requests.service_id', '=', 'services.id')
            ->select('services.name', DB::raw('count(*) as count'))
            ->groupBy('services.name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        $requestsPerOffice = DB::table('government_offices')
            ->leftJoin('services', 'services.office_id', '=', 'government_offices.id')
            ->leftJoin('service_requests', 'service_requests.service_id', '=', 'services.id')
            ->select(
                'government_offices.id as office_id',
                'government_offices.name',
                DB::raw('COUNT(service_requests.id) as count')
            )
            ->groupBy('government_offices.id', 'government_offices.name')
            ->orderByDesc('count')
            ->get();

        $apptDateColumn = Schema::hasColumn('appointments', 'appointment_date') ? 'appointment_date' : 'date';

        $totalAppointments = DB::table('appointments')->count();
        $upcomingAppointments = DB::table('appointments')
            ->where($apptDateColumn, '>=', now()->toDateString())
            ->count();

        $recentRequests = DB::table('service_requests')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $recentPayments = DB::table('payments')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $recentAppointments = DB::table('appointments')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return view('admin.analytics', compact(
            'totalRequests',
            'pendingRequests',
            'completedRequests',
            'requestsByStatus',
            'totalRevenue',
            'paymentsByMethod',
            'popularServices',
            'requestsPerOffice',
            'totalAppointments',
            'upcomingAppointments',
            'recentRequests',
            'recentPayments',
            'recentAppointments'
        ));
    }
}
