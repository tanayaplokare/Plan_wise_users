<?php

namespace App\Http\Controllers;


use App\Models\OriginalUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class UploadController extends Controller
{
    public function indexOriginal()
    {
        // Add authorization if needed
        $originalFiles = OriginalUpload::latest()->paginate(15); // Get latest, paginated
        return view('uploads.index', compact('originalFiles'));
    }

    // Show Upload Form
    public function createOriginal()
    {
        // Add authorization if needed
        return view('uploads.create');
    }

    // Store Uploaded File
    public function storeOriginal(Request $request)
    {
        // Add authorization if needed

        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240', // Example validation (10MB max)
        ]);

        $file = $request->file('file');
        $originalFilename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $storedFilename = time() . '_' . Str::random(10) . '.' . $extension;
        $directory = 'original_files'; // Store originals separately

        // Store the file
        $path = $file->storeAs($directory, $storedFilename, 'local'); // Returns 'original_files/filename.ext'

        if (!$path) {
            return back()->with('error', 'Could not store the uploaded file.')->withInput();
        }

        // Create database record
        OriginalUpload::create([
            'original_filename' => $originalFilename,
            'stored_filename' => $storedFilename,
            'file_path' => $path,
            // 'user_id' => auth()->id(), // If tracking user
        ]);

        return redirect()->route('uploads.index')->with('success', 'File uploaded successfully.');
    }

    // Delete Original Upload and File
    public function destroyOriginal(OriginalUpload $originalUpload)
    {

        
        
         if (auth()->user()->role !== 'admin') { // Simple example
             return redirect()->route('uploads.index')->with('error', 'Unauthorized action.');
         }

        try {
            
             $originalUpload->delete();
            return redirect()->route('uploads.index')->with('success', 'Original file record deleted successfully.');
        } catch (\Exception $e) {
            Log::error("Error deleting original upload ID {$originalUpload->id}: " . $e->getMessage());
            return redirect()->route('uploads.index')->with('error', 'Could not delete the original file record.');
        }
    }
}
