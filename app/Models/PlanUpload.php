<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanUpload extends Model
{
    use HasFactory;
    protected $fillable = ['plan_id', 'original_filename', 'stored_filename','upload_path'];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
