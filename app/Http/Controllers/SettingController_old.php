<?php

namespace App\Http\Controllers;

use App\Models\FilterUpload;
use App\Models\Keyword;
use App\Models\Plan;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use League\Csv\Reader;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter; // For writing filtered CSV
use Illuminate\Support\Str; // For Str::contains
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Facades\Log;
class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $filteredFiles = FilterUpload::orderBy('id' ,'desc')->get();
        return view('settings.index' ,compact('filteredFiles'));
    }

    public function uploadForm()
    {
        return view('settings.form');
    }
    public function uploadFile(Request $request)
    {
        $request->validate([
            // Add mimes validation for better security/robustness
            'file' => 'required|file|mimes:csv,txt,xlsx,xls',
        ]);

        $file = $request->file('file');
        $originalFilename = $file->getClientOriginalName();
        // Store with a unique name to avoid conflicts
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads', $filename, 'local'); // Store in storage/app/uploads

        $filePath = storage_path('app/' . $path);

        $headers = [];
        try {
            // Use PhpSpreadsheet to load the file
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            // Get headers from the first row
            $headerRow = $sheet->getRowIterator(1, 1)->current();
            if ($headerRow) {
                $cellIterator = $headerRow->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if null
                foreach ($cellIterator as $cell) {
                    $headers[] = $cell->getValue();
                }
                // Remove null/empty headers if any at the end
                $headers = array_filter($headers, function($value) { return !is_null($value) && $value !== ''; });
            }
        } catch (\Exception $e) {
            // Handle exceptions during file reading (e.g., corrupted file)
            Storage::disk('local')->delete($path); // Clean up uploaded file
            return back()->withErrors(['file' => 'Could not read the file headers. Error: ' . $e->getMessage()])->withInput();
        }

        if (empty($headers)) {
             Storage::disk('local')->delete($path); // Clean up uploaded file
            return back()->withErrors(['file' => 'Could not extract headers from the file or the file is empty.'])->withInput();
        }

        // Fetch keywords from the database
        $keywords = Keyword::pluck('keyword', 'id')->toArray(); // Get id => keyword map or just keywords

        return view('settings.filter_form', [
            'filename' => $filename, // Pass the stored filename
            'original_filename' => $originalFilename, // Pass original filename if needed in view/form
            'columns' => $headers,
            'keywords' => $keywords, // Pass keywords to the view
        ]);
    }

    public function processFilter(Request $request)
    {
        $request->validate([
            'filename' => 'required|string',
            'column' => 'required|string',
            'keywords' => 'required|array|min:1', // Ensure at least one keyword is selected
            'keywords.*' => 'required|string', // Ensure every selected keyword is a string
            'original_filename' => 'nullable|string',
        ]);

        $filename = $request->input('filename');
        $originalFilename = $request->input('original_filename');
        $columnName = trim($request->input('column'));
        // *** Get the raw keywords directly from the request for saving later ***
        $selectedKeywordsFromRequest = $request->input('keywords');

        // *** Prepare keywords FOR FILTERING: lowercase, trim, remove empty ***
        $keywordsForFiltering = array_filter(
            array_map('strtolower', array_map('trim', $selectedKeywordsFromRequest)),
            function($kw) { return !empty($kw); }
        );


        $uploadPath = 'uploads/' . $filename;
        if (!Storage::disk('local')->exists($uploadPath)) {
            return redirect()->route('setting.upload_form')->withErrors(['file' => 'Original file not found. Please upload again.']);
        }

        $filteredRecords = []; // Array to hold rows that DO NOT contain keywords
        $headerRow = [];
        $inputRowCount = 0;
        $outputRowCount = 0;

        try {
            $spreadsheet = IOFactory::load(Storage::path($uploadPath));
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestDataRow();
            $highestColumn = $sheet->getHighestDataColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

            // Read Header Row (Row 1) & find filter column index
            $filterColumnIndex = false; // 1-based index
            for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                 $headerValueRaw = $sheet->getCell([$col, 1])->getValue();
                 $headerValue = $headerValueRaw !== null ? trim((string)$headerValueRaw) : '';
                 $headerRow[] = $headerValue; // Store the cleaned header
                 if ($filterColumnIndex === false && strcasecmp($headerValue, $columnName) == 0) {
                     $filterColumnIndex = $col; // Found column, store 1-based index
                 }
            }

            if ($filterColumnIndex === false) {
                 return back()->withErrors(['column' => 'Selected filter column "' . e($columnName) . '" not found in file header. Headers Found: '. implode(', ', array_map('e', $headerRow))])->withInput();
            }

            // Read data rows (starting from row 2)
            for ($row = 2; $row <= $highestRow; ++$row) {
                $inputRowCount++;
                $rowData = []; // Holds data for the entire current row
                 for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                    $rowData[] = $sheet->getCell([$col, $row])->getValue(); // Get raw value for output
                 }

                $cell = $sheet->getCell([$filterColumnIndex, $row]);
                $columnValue = $cell->getValue(); // Use raw value for comparison
                $columnValueStr = trim(strtolower((string)$columnValue));

                $rowContainsKeyword = false; // Assume NO keyword found initially

                if (!empty($columnValueStr)) {
                    foreach ($keywordsForFiltering as $keyword) {
                        if (Str::contains($columnValueStr, $keyword)) {
                            $rowContainsKeyword = true; // Set flag to TRUE: This row should be removed

                            break; // Found a keyword, no need to check others for this row
                        }
                    }
                }

                if (!$rowContainsKeyword) {
                    $filteredRecords[] = $rowData; // Add the row data to the results
                    $outputRowCount++;
                  
                } 
               

            } // End row loop

        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            Log::error('Spreadsheet Processing Error', ['filename' => $filename, 'exception' => $e]);
            return back()->withErrors(['file' => 'Error reading spreadsheet file: ' . $e->getMessage()])->withInput();
        } catch (\Exception $e) {
            Log::error('General File Processing Error', ['filename' => $filename, 'exception' => $e]);
            return back()->withErrors(['file' => 'Error processing file: ' . $e->getMessage()])->withInput();
        }

        // --- Save the filtered data to a new CSV file ---
        $originalExtension = pathinfo($filename, PATHINFO_EXTENSION);
        $baseFilename = pathinfo($filename, PATHINFO_FILENAME);
        $filteredFilename = 'clean_' . $baseFilename . '_' . time()  . '.' . $originalExtension; 
        $filteredDirectory = 'filtered_files';
        $filteredRelativePath = $filteredDirectory . '/' . $filteredFilename;
        $filteredFullPath = storage_path('app/' . $filteredRelativePath);

        Storage::disk('local')->makeDirectory($filteredDirectory);

        try {
            $writerSpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $writerSheet = $writerSpreadsheet->getActiveSheet();
            $writerSheet->fromArray($headerRow, NULL, 'A1'); // Use the header read from the file
            $writerSheet->fromArray($filteredRecords, NULL, 'A2'); // Write kept rows

            $writer = new CsvWriter($writerSpreadsheet);
            $writer->setUseBOM(true); // For better Excel compatibility
            $writer->save($filteredFullPath);

        } catch (\Exception $e) {
             Log::error('File Saving Error', ['filename' => $filteredFullPath, 'exception' => $e]);
             return redirect()->route('filtered.uploads.list')->withErrors(['file' => 'Could not save filtered file: ' . $e->getMessage()]);
        }


        // --- Save information to the filter_uploads table ---
        try {
           
            $filterRec = FilterUpload::create([
                'original_filename' => $originalFilename,
                'filtered_column' => $columnName, // Save the column name that was filtered
               
                'selected_keywords' => $selectedKeywordsFromRequest,
                'file_path' => $filteredRelativePath, // Save relative path
            ]);

          

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database Saving Error', ['exception' => $e, 'data' => $selectedKeywordsFromRequest]);
         
            return redirect()->route('filtered.uploads.list')->withErrors(['database' => 'Could not save filter information to database. Please check logs. DB Error: ' . $e->getMessage()]);
        } catch (\Exception $e) { // Catch other potential errors during create
             Log::error('General DB Saving Error', ['exception' => $e]);
              return redirect()->route('filtered.uploads.list')->withErrors(['database' => 'An unexpected error occurred while saving filter information.']);
        }

        $rowsRemoved = $inputRowCount - $outputRowCount;
        return redirect()->route('filtered.uploads.list')->with('success', "File filtered successfully. Processed $inputRowCount data rows, removed $rowsRemoved rows, kept $outputRowCount rows");
    }
    public function filteredUploadsList()
    {
        // Fetch files, perhaps display selected keywords too
        $filteredFiles = FilterUpload::orderBy('id', 'desc')->get();
        return view('settings.index', compact('filteredFiles'));
    }

     public function downloadFilteredFile(FilterUpload $filterUpload)
     {
        $filePath = storage_path('app/' . $filterUpload->file_path);

        // Use Storage facade for existence check for consistency
        if (!Storage::disk('local')->exists($filterUpload->file_path)) {
             return back()->withErrors(['file' => 'Filtered file not found. It might have been deleted.']);
        }

        // Generate a user-friendly download name (e.g., the 'clean_...' name)
        $downloadFilename = basename($filterUpload->file_path);

        return Storage::disk('local')->download($filterUpload->file_path, $downloadFilename);
        // Or use response()->download if you need more header control:
        // return response()->download($filePath, $downloadFilename);
    }


    public function filterAndDownload(Request $request)
    {
        $request->validate([
            'filename' => 'required',
            'column' => 'required',
        ]);

        $filename = $request->input('filename');
        $columnName = $request->input('column');
        $filePath = storage_path('app/uploads/' . $filename);

        if (!File::exists($filePath)) {
            return back()->withErrors(['file' => 'File not found.']);
        }

        $reader = Reader::createFromPath($filePath, 'r');
        $reader->setHeaderOffset(0);
        $header = $reader->getHeader();
        //dd($header);
        $records = iterator_to_array($reader);

       
    // Fetch all keywords from the database
    $masterKeywords = Keyword::pluck('keyword')->toArray();

    $filteredRecords = [];
    foreach ($records as $record) {
        $columnValue = isset($record[$columnName]) ? trim(strtolower($record[$columnName])) : null;
        $hasKeyword = false;

        if ($columnValue !== null) {
            foreach ($masterKeywords as $keyword) {
                if (str_contains($columnValue, strtolower(trim($keyword)))) {
                    $hasKeyword = true;
                    break;
                }
            }
        }

        if (!$hasKeyword) {
            $filteredRecords[] = $record;
        }
    }

    // dd($filteredRecords); // Uncomment this to inspect the filtered records

    // Create a new CSV writer
    $writer = Writer::createFromString('');
    $writer->insertOne($header); // Add the header row
    $writer->insertAll($filteredRecords);

    $filteredFilename = 'filtered_' . $filename;
    $filteredFilePath = storage_path('app/filtered_files/' . $filteredFilename);

    // Save the filtered file to storage
    Storage::disk('local')->put('filtered_files/' . $filteredFilename, $writer->toString());

    // Prepare the response for download
    $response = new StreamedResponse(function () use ($writer) {
        echo $writer->toString();
    }, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="' . $filteredFilename . '"',
    ]);

    return $response;
    }


    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(FilterUpload $filterUpload)
    {
       
         if (auth()->user()->role !== 'admin') { // Simple role check example
             return redirect()->route('filtered.uploads.list')->with('error', 'You do not have permission to delete this record.');
         }


        try {
            $filePath = $filterUpload->file_path; // Get the relative path from the record

            // 1. Delete the physical file from storage
            if ($filePath && Storage::disk('local')->exists($filePath)) {
                Storage::disk('local')->delete($filePath);
                
            }

           
            $filterUpload->delete();

            return redirect()->route('filtered.uploads.list')->with('success', 'Filtered record and associated file deleted successfully.');

        } catch (\Exception $e) {
            Log::error("Error deleting filtered upload record ID {$filterUpload->id}: " . $e->getMessage());
            return redirect()->route('filtered.uploads.list')->with('error', 'An error occurred while deleting the record. Please check the logs.');
        }
    }
}
