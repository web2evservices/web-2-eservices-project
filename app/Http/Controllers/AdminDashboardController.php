<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Office;
use App\Models\Municipality;
use App\Models\Notifications;

class AdminDashboardController extends Controller
{
     public function index()
    {
        return view('admin.dashboard', [
            'users' => User::count(),
            'offices' => Office::count(),
            'municipalities' => Municipality::count(),
            'activeOffices' => Office::where('is_active',1)->count()
            ,'adminActivities' => Notifications::where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ]);
    }
}
