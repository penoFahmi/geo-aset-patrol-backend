<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id', 'asset_id', 'sequence_order', 'is_visited', 'visited_at'
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
