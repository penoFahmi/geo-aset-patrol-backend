<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AssetController extends Controller
{
    /**
     * POST /api/assets
     * Menyimpan data aset baru dari Android.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'address'      => 'required|string',
            'area_size'    => 'required|numeric',
            'status'       => 'required|in:aman,sengketa,tanah_pemda,sewa',
            'geojson_data' => 'required',
            'centroid_lat' => 'required|numeric',
            'centroid_lng' => 'required|numeric',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['meta' => ['code' => 422, 'status' => 'error'], 'data' => $validator->errors()], 422);
        }

        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('assets', 'public');
            }

            $asset = Asset::create([
                'name'         => $request->name,
                'address'      => $request->address,
                'area_size'    => $request->area_size,
                'status'       => $request->status,
                'geojson_data' => json_decode($request->geojson_data) ?? $request->geojson_data,
                'centroid_lat' => $request->centroid_lat,
                'centroid_lng' => $request->centroid_lng,
                'image_path'   => $imagePath,
                'created_by'   => $request->user()->id,
            ]);

            return response()->json([
                'meta' => ['code' => 201, 'status' => 'success', 'message' => 'Aset berhasil disimpan'],
                'data' => $asset
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['meta' => ['code' => 500, 'status' => 'error'], 'data' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/assets
     * Mengambil semua data aset (untuk ditampilkan di Peta).
     */
    public function index()
    {
        $assets = Asset::with('creator:id,name')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'meta' => ['code' => 200, 'status' => 'success'],
            'data' => $assets
        ]);
    }
}
