<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; 

class OriginalUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_filename',
        'stored_filename',
        'file_path',
    ];

    public function getStoragePathAttribute(): string
    {
        return Storage::disk('local')->path($this->file_path);
    }

     // Relationship: An original upload can lead to many filtered results
     public function filteredUploads()
     {
         return $this->hasMany(FilterUpload::class);
     }

    // Optional: Override deleting event to also delete the file
    protected static function booted(): void
    {
        static::deleting(function (OriginalUpload $originalUpload) {
            if ($originalUpload->file_path && Storage::disk('local')->exists($originalUpload->file_path)) {
                Storage::disk('local')->delete($originalUpload->file_path);
            }
        });
    }
}
