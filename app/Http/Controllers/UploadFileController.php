<?php

namespace App\Http\Controllers;
use Illuminate\Http\Exceptions\PostTooLargeException;

use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\PlanUpload;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadFileController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
           
            $uploads = PlanUpload::with('plan')->orderBy('id', 'desc')->get();
        } else {
            
            $uploads = PlanUpload::with('plan')
                ->whereHas('plan', function ($query) use ($user) {
                    $query->whereIn('id', function ($subQuery) use ($user) {
                        $subQuery->select('plan_id')
                            ->from('user_plans')
                            ->where('user_id', $user->id);
                    });
                })
                ->orderBy('id', 'desc')
                ->get();
        }

        return view('plan_uploads.index', compact('uploads'));
    }


    public function create()
    {
        $plans = Plan::where('status' ,'active')->get();
        return view('plan_uploads.form', compact('plans'));
    }
    public function store(Request $request)
    {
        try{
            Log::info('Uploading File:', $request->all());
    
            // Validate inputs
            $request->validate([
                'plan_id' => 'required|exists:plans,id',
                'file' => 'required|file|mimes:zip|max:20480', // ZIP files only, Max 20MB
                'upload_date' => 'required|date', 
            ]);
        
            // Check if file is uploaded
            if (!$request->hasFile('file')) {
                return back()->withErrors(['file' => 'No file was uploaded. Please try again.']);
            }
        
            $file = $request->file('file');
        
            // Convert file size to MB
            $fileSizeMB = $file->getSize() / 1024 / 1024; // Convert to MB
        
            // If file size is greater than 20MB, return error
            if ($fileSizeMB > 20) {
                return back()->withErrors(['file' => 'File size exceeds 20MB. Please upload a smaller file.']);
            }
        
            $plan = Plan::findOrFail($request->plan_id);
            $formattedDate = \Carbon\Carbon::parse($request->upload_date)->format('Ymd');
        
            // Get file details
            $originalFilename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $storedFilename = "{$formattedDate}_{$plan->plan_name}_{$plan->plan_type}.{$extension}";
        
            // Store file
            $path = $file->storeAs('uploads', $storedFilename, 'public');
        
            if (!$path) {
                return back()->withErrors(['file' => 'File failed to upload. Please try again.']);
            }
        
            // Save record in database
            PlanUpload::create([
                'plan_id' => $plan->id,
                'original_filename' => $originalFilename,
                'stored_filename' => $storedFilename,
                'upload_path' => $storedFilename,
            ]);
        
            return redirect()->route('planuploads.index')->with('success', 'File uploaded successfully!');
        

        }catch (PostTooLargeException $e) {
            return back()->with('error', 'The uploaded file is too large. Please try again with a smaller file.');
        }
    }

    public function download($id)
    {
        $upload = PlanUpload::findOrFail($id);
        return response()->download(storage_path("app/public/uploads/{$upload->stored_filename}"));
    }

    public function destroy($id)
    {
        $upload = PlanUpload::findOrFail($id);
        Storage::delete("public/uploads/{$upload->stored_filename}");
        $upload->delete();

        return redirect()->route('planuploads.index')->with('success', 'File deleted successfully!');
    }
}
