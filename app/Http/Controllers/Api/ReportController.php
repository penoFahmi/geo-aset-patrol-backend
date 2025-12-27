<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentDetail;
use App\Models\PatrolReport;
use App\Services\RouteOptimizerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    /**
     * POST /api/reports
     * Petugas mengirim laporan kerja.
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'assignment_detail_id' => 'required|exists:assignment_details,id',
            'latitude'             => 'required|numeric',
            'longitude'            => 'required|numeric',
            'photo'                => 'required|image|max:5120',
            'notes'                => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['meta' => ['code' => 422, 'status' => 'error'], 'data' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // 2. Ambil Data Detail Tugas & Asetnya
            $detail = AssignmentDetail::with('asset', 'assignment')->find($request->assignment_detail_id);

            // Cek Security: Apakah yang lapor benar petugas yang ditugaskan?
            if ($detail->assignment->officer_id != $request->user()->id) {
                return response()->json(['meta' => ['code' => 403, 'status' => 'error', 'message' => 'Anda tidak berhak melapor tugas ini'], 'data' => null], 403);
            }

            // 3. HITUNG JARAK (Validasi Radius Server-Side)
            // Reuse rumus Haversine dari Service yang kemarin kamu buat
            // Kita hitung jarak antara Posisi Petugas (Request) vs Posisi Aset (Database)
            $optimizer = new RouteOptimizerService();

            $dist = $this->calculateHaversine(
                $request->latitude, $request->longitude,
                $detail->asset->centroid_lat, $detail->asset->centroid_lng
            );

            // Tentukan Validitas (Misal toleransi 50 meter)
            $isValid = $dist <= 50;

            // 4. Upload Foto
            $photoPath = $request->file('photo')->store('reports', 'public');

            // 5. Simpan Laporan
            $report = PatrolReport::create([
                'assignment_detail_id' => $request->assignment_detail_id,
                'latitude'             => $request->latitude,
                'longitude'            => $request->longitude,
                'distance_deviation'   => $dist,
                'is_valid_radius'      => $isValid,
                'photo_path'           => $photoPath,
                'notes'                => $request->notes
            ]);

            // 6. Update Status Tugas
            // Tandai aset ini SUDAH DIKUNJUNGI
            $detail->update([
                'is_visited' => true,
                'visited_at' => now()
            ]);

            // Cek: Apakah semua aset di surat tugas ini sudah selesai?
            // Kalau ya, update status Surat Tugas jadi 'completed'
            $pendingCount = AssignmentDetail::where('assignment_id', $detail->assignment_id)
                                            ->where('is_visited', false)
                                            ->count();

            if ($pendingCount == 0) {
                $detail->assignment->update(['status' => 'completed']);
            } else {
                $detail->assignment->update(['status' => 'in_progress']);
            }

            DB::commit();

            return response()->json([
                'meta' => ['code' => 201, 'status' => 'success', 'message' => 'Laporan berhasil dikirim'],
                'data' => [
                    'report' => $report,
                    'is_radius_valid' => $isValid // Beritahu petugas kalau dia kejauhan
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['meta' => ['code' => 500, 'status' => 'error'], 'data' => $e->getMessage()], 500);
        }
    }

    // Helper: Rumus Jarak (Copy dari Service kemarin biar controller mandiri)
    private function calculateHaversine($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
