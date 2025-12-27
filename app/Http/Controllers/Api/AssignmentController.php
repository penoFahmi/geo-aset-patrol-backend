<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentDetail;
use App\Models\Asset;
use App\Services\RouteOptimizerService; // <--- Import Service Otak Kita
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AssignmentController extends Controller
{
    protected $optimizer;

    // Inject Service lewat Constructor
    public function __construct(RouteOptimizerService $optimizer)
    {
        $this->optimizer = $optimizer;
    }

    /**
     * POST /api/assignments
     * Admin membuat surat tugas baru + Optimasi Rute Otomatis.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'officer_id'      => 'required|exists:users,id',
            'assignment_date' => 'required|date',
            'description'     => 'nullable|string',
            'asset_ids'       => 'required|array|min:1',
            'asset_ids.*'     => 'exists:assets,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['meta' => ['code' => 422, 'status' => 'error'], 'data' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $assignment = Assignment::create([
                'admin_id'        => $request->user()->id,
                'officer_id'      => $request->officer_id,
                'assignment_date' => $request->assignment_date,
                'description'     => $request->description,
                'status'          => 'pending',
            ]);

            // 3. Ambil Data Aset yang Dipilih
            $selectedAssets = Asset::whereIn('id', $request->asset_ids)->get();

            // 4. JALANKAN ALGORITMA!
            // Hasilnya adalah array aset yang sudah urut 1, 2, 3...
            $optimizedAssets = $this->optimizer->optimize($selectedAssets);

            // 5. Simpan Detail Rute ke Database
            foreach ($optimizedAssets as $assetData) {
                AssignmentDetail::create([
                    'assignment_id'  => $assignment->id,
                    'asset_id'       => $assetData['id'],
                    'sequence_order' => $assetData['sequence_order'],
                    'is_visited'     => false,
                ]);
            }

            DB::commit();

            return response()->json([
                'meta' => ['code' => 201, 'status' => 'success', 'message' => 'Tugas berhasil dibuat & rute dioptimalkan'],
                'data' => $assignment->load('details.asset')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['meta' => ['code' => 500, 'status' => 'error'], 'data' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/assignments
     * List tugas (Bisa difilter buat Petugas lihat tugas dia sendiri).
     */
    public function index(Request $request)
    {
        $query = Assignment::with(['officer', 'details.asset'])
            ->orderBy('assignment_date', 'desc');

        if ($request->user()->role == 'officer') {
            $query->where('officer_id', $request->user()->id);
        }

        $assignments = $query->get();

        return response()->json([
            'meta' => ['code' => 200, 'status' => 'success'],
            'data' => $assignments
        ]);
    }
}
