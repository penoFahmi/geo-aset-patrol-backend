<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'area_size',
        'status',
        'geojson_data',
        'centroid_lat',
        'centroid_lng',
        'image_path',
        'created_by'
    ];

    /**
     * Tips Pro: Casting
     * Mengubah data GeoJSON (String) di database menjadi Array/Object PHP otomatis.
     */
    protected $casts = [
        'geojson_data' => 'array',
        'area_size'    => 'double',
        'centroid_lat' => 'double',
        'centroid_lng' => 'double',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
