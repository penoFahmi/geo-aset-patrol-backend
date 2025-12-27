<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Region extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'geojson_data', 'fill_color', 'stroke_color'];

    protected $casts = [
        'geojson_data' => 'array',
    ];
}
