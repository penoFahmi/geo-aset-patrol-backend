<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Assignment;
use App\Models\User;
use App\Models\PatrolReport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard
     * Menyediakan data rekapitulasi untuk Halaman Utama Admin.
     */
    public function index()
    {
        // 1. Statistik Kartu Atas
        $totalAssets = Asset::count();
        $totalOfficers = User::where('role', 'officer')->count();

        // Tugas Hari Ini
        $assignmentsToday = Assignment::whereDate('assignment_date', Carbon::today())->count();
        $pendingAssignments = Assignment::whereDate('assignment_date', Carbon::today())
                                        ->where('status', '!=', 'completed')
                                        ->count();

        // 2. Statistik Status Aset (Untuk Pie Chart)
        // Hasilnya: [{"status": "aman", "total": 50}, {"status": "sengketa", "total": 5}]
        $assetStatusStats = Asset::select('status', \DB::raw('count(*) as total'))
                                 ->groupBy('status')
                                 ->get();

        // 3. Aktivitas Terbaru (Recent Activity)
        // Menampilkan 5 laporan terakhir yang masuk
        $recentReports = PatrolReport::with(['assignmentDetail.asset', 'assignmentDetail.assignment.officer'])
                                     ->orderBy('created_at', 'desc')
                                     ->take(5)
                                     ->get()
                                     ->map(function ($report) {
                                         // Kita format datanya biar Frontend enak bacanya
                                         return [
                                             'id' => $report->id,
                                             'officer_name' => $report->assignmentDetail->assignment->officer->name,
                                             'asset_name' => $report->assignmentDetail->asset->name,
                                             'submitted_at' => $report->created_at->diffForHumans(), // "2 minutes ago"
                                             'is_valid' => $report->is_valid_radius,
                                         ];
                                     });

        $timeLimit = Carbon::now()->subMinutes(10);
        $activeOfficers = User::where('role', 'officer')
                              ->where('last_seen_at', '>=', $timeLimit)
                              ->whereNotNull('last_latitude')
                              ->get(['id', 'name', 'last_latitude', 'last_longitude', 'last_seen_at']);

        return response()->json([
            'meta' => ['code' => 200, 'status' => 'success'],
            'data' => [
                'cards' => [
                    'total_assets' => $totalAssets,
                    'total_officers' => $totalOfficers,
                    'assignments_today' => $assignmentsToday,
                    'pending_today' => $pendingAssignments,
                    'active_officers' => $activeOfficers
                ],
                'charts' => [
                    'asset_status' => $assetStatusStats
                ],
                'recent_activities' => $recentReports,
                'live_locations' => $activeOfficers
            ]
        ]);
    }
}
