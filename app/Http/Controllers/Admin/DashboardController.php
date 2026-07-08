<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_employees' => User::where('type', 'employee')->count(),
            'active_employees' => User::where('type', 'employee')->active()->count(),
            'total_customers' => User::where('type', 'customer')->count(),
            'unread_notifications' => auth()->user()->unreadNotifications()->count(),
        ];

        $recentUsers = User::latest()->take(8)->get();

        return view('dashboard.index', compact('stats', 'recentUsers'));
    }
}
