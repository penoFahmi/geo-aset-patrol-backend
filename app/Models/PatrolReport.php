<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatrolReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_detail_id',
        'latitude',
        'longitude',
        'distance_deviation',
        'is_valid_radius',
        'photo_path',
        'notes'
    ];

    // Relasi balik ke Detail Tugas
    public function assignmentDetail()
    {
        return $this->belongsTo(AssignmentDetail::class);
    }
}
