<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class FilterUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_upload_id',
        'original_filename', 
        'filtered_column',
        'file_path' ,
        'selected_keywords'
    ];


    protected $casts = [
        'selected_keywords' => 'array', 
        'filtered_column' => 'array', 
    ];

    public function originalUpload()
    {
        return $this->belongsTo(OriginalUpload::class);
    }

    // Optional: Override deleting event to also delete the filtered file
     protected static function booted(): void
     {
         static::deleting(function (FilterUpload $filterUpload) {
             if ($filterUpload->file_path && Storage::disk('local')->exists($filterUpload->file_path)) {
                 Storage::disk('local')->delete($filterUpload->file_path);
             }
         });
     }
}
