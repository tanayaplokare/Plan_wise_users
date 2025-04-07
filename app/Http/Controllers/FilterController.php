<?php

namespace App\Http\Controllers;

use App\Models\FilterUpload;
use App\Models\Keyword;
use App\Models\OriginalUpload; // Import OriginalUpload
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;


class FilterController extends Controller
{
    public function showFilterForm(OriginalUpload $originalUpload)
    {
        // Add authorization if needed

        $headers = [];
        $keywords = Keyword::orderBy('keyword')->pluck('keyword')->toArray(); // Get keywords from DB

        try {
            if (!Storage::disk('local')->exists($originalUpload->file_path)) {
                 return redirect()->route('uploads.index')->with('error', 'Original file not found for filtering.');
            }
            // Use PhpSpreadsheet to get headers
            $spreadsheet = IOFactory::load($originalUpload->storagePath); // Use accessor here
            $sheet = $spreadsheet->getActiveSheet();
            $headerRowIterator = $sheet->getRowIterator(1, 1)->current();
            if ($headerRowIterator) {
                $cellIterator = $headerRowIterator->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                foreach ($cellIterator as $cell) {
                    $headerValue = $cell->getValue();
                    // Stop at first completely empty header cell if desired
                    // if ($headerValue === null || $headerValue === '') break;
                    if ($headerValue !== null) { // Only add non-null headers
                        $headers[] = trim((string)$headerValue);
                    }
                }
                $headers = array_filter($headers); // Remove empty strings just in case
            }
        } catch (\Exception $e) {
             Log::error("Error reading headers for OriginalUpload ID {$originalUpload->id}: " . $e->getMessage());
            return redirect()->route('uploads.index')->with('error', 'Could not read headers from the selected file.');
        }

        if (empty($headers)) {
            return redirect()->route('uploads.index')->with('error', 'No headers found in the selected file.');
        }

        // Pass data to the view
        return view('uploads.filter_form', compact('originalUpload', 'headers', 'keywords'));
    }

    public function processFilter1(Request $request)
    {
        // Add authorization if needed

        $request->validate([
            'original_upload_id' => 'required|exists:original_uploads,id',
            'column' => 'required|array|min:1',       // Expecting an array of column names
            'column.*' => 'required|string',          // Each item in the array must be a string
            'keywords' => 'required|array|min:1',     // Expecting an array of keywords
            'keywords.*' => 'required|string',        // Each item must be a string
        ]);

        $originalUploadId = $request->input('original_upload_id');
        $selectedColumnNames = $request->input('column'); // Array of column names from form
        $selectedKeywords = $request->input('keywords');    // Array of keywords from form

        // Prepare keywords for efficient filtering: lowercase, trim, remove empty
        $keywordsForFiltering = array_filter(
            array_map('strtolower', array_map('trim', $selectedKeywords)),
            function($kw) { return !empty($kw); }
        );

        if (empty($keywordsForFiltering)) {
             return back()->withErrors(['keywords' => 'No valid keywords provided after cleaning.'])->withInput();
        }

        // Find original file record and check physical file existence
        $originalUpload = OriginalUpload::find($originalUploadId);
        if (!$originalUpload || !Storage::disk('local')->exists($originalUpload->file_path)) {
             return redirect()->route('uploads.index')->with('error', 'Original file not found or inaccessible.');
        }

        // --- Filtering Logic ---
        $filteredRecords = []; // Holds rows that PASS the filter
        $headerRow = [];
        $inputRowCount = 0;
        $outputRowCount = 0;
        $filterColumnIndices = []; // Holds the 1-based indices of columns to check

        try {
            $spreadsheet = IOFactory::load($originalUpload->storagePath);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestDataRow();
            $highestColumn = $sheet->getHighestDataColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

            // --- Map Header Names to Indices & Find Selected Column Indices ---
            $headerMap = []; // Stores lower_header_name => 1-based index
            for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                 $headerValueRaw = $sheet->getCell([$col, 1])->getValue();
                 $headerValue = $headerValueRaw !== null ? trim((string)$headerValueRaw) : '';
                 $headerRow[] = $headerValue; // Keep original case for output header
                 if(!empty($headerValue)) {
                     $headerMap[strtolower($headerValue)] = $col; // Use lowercase for mapping
                 }
            }

            $notFoundColumns = [];
            // Get the 1-based indices for the columns selected by the user
            foreach($selectedColumnNames as $colName) {
                $lowerColName = strtolower(trim($colName));
                if (isset($headerMap[$lowerColName])) {
                    $filterColumnIndices[] = $headerMap[$lowerColName];
                } else {
                    $notFoundColumns[] = $colName;
                }
            }

            // Handle errors if columns selected in the form are not found in the file
            if (!empty($notFoundColumns)) {
                 return back()->withErrors(['column' => 'The following selected columns were not found in the file header: ' . implode(', ', array_map('e', $notFoundColumns))])->withInput();
            }
            if (empty($filterColumnIndices)) {
                 return back()->withErrors(['column' => 'No valid columns to filter were identified in the header.'])->withInput();
             }
            // --- End Column Mapping ---


            // --- Process Data Rows ---
            for ($row = 2; $row <= $highestRow; ++$row) {
                $inputRowCount++;
                $rowData = []; // Store data for the entire current row
                 for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                    $rowData[] = $sheet->getCell([$col, $row])->getValue();
                 }

                // Flag to determine if the current row should be removed
                $rowContainsKeyword = false;

                // --- Loop through the specific columns the user selected ---
                foreach ($filterColumnIndices as $colIndexToCheck) {
                    // Get the value from the current cell being checked
                    $cellValueToCheck = $sheet->getCell([$colIndexToCheck, $row])->getValue();
                    $cellValueStringLower = trim(strtolower((string)$cellValueToCheck));

                    // Only check keywords if the cell has content
                    if (!empty($cellValueStringLower)) {
                         // --- Loop through the user-selected keywords ---
                        foreach ($keywordsForFiltering as $keyword) {
                            // Check if the cell's content contains the current keyword
                            if (Str::contains($cellValueStringLower, $keyword)) {
                                // *** MATCH FOUND! ***
                                $rowContainsKeyword = true; // Mark this row for removal
                                // Break out of BOTH loops (keywords and columns) for THIS ROW.
                                // No need to check further keywords or columns for this row.
                                break 2;
                            }
                        } // End keyword loop
                    } // End if cell not empty

                    // If $rowContainsKeyword became true in the inner loop, the 'break 2'
                    // already exited this outer column loop as well.

                } // End column loop for the current row

                // --- Decision: Keep or Discard Row ---
                // Keep the row ONLY IF the $rowContainsKeyword flag is still FALSE
                if (!$rowContainsKeyword) {
                    $filteredRecords[] = $rowData;
                    $outputRowCount++;
                }
                // --- End Decision ---

            } // End data row loop
        } catch (\Exception $e) {
            Log::error("Error filtering OriginalUpload ID {$originalUpload->id}: " . $e->getMessage());
            return back()->with('error', 'An error occurred during the filtering process. Please check logs.')->withInput();
        }

        // --- Save Filtered File (Outputting as CSV) ---
        $baseFilename = pathinfo($originalUpload->stored_filename, PATHINFO_FILENAME);
        // Decide output extension (forcing CSV here)
        $outputExtension = 'csv';
        $filteredFilename = 'filtered_' . $baseFilename . '_' . time() . '.' . $outputExtension;
        $filteredDirectory = 'filtered_files';
        $filteredRelativePath = $filteredDirectory . '/' . $filteredFilename;
        $filteredFullPath = storage_path('app/' . $filteredRelativePath);

        Storage::disk('local')->makeDirectory($filteredDirectory);

        try {
            $writerSpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $writerSheet = $writerSpreadsheet->getActiveSheet();
            $writerSheet->fromArray($headerRow, NULL, 'A1'); // Use actual header read
            $writerSheet->fromArray($filteredRecords, NULL, 'A2'); // Write kept rows
            $writer = new CsvWriter($writerSpreadsheet);      // Write as CSV
            $writer->setUseBOM(true);                        // For Excel compatibility
            $writer->save($filteredFullPath);
        } catch (\Exception $e) {
             Log::error('Filtered File Saving Error', ['filename' => $filteredFullPath, 'exception' => $e]);
             return redirect()->route('filtered.index')->withErrors(['file' => 'Could not save the filtered file: ' . $e->getMessage()]);
        }

        // --- Create FilterUpload Record ---
        try {
            // Ensure your Model and DB Table use 'filtered_column' or 'filtered_columns' consistently
            $columnDataField = 'filtered_column'; // Or 'filtered_columns' if you renamed it

            FilterUpload::create([
                'original_upload_id' => $originalUpload->id,
                // Save the array of COLUMN NAMES selected by the user
                $columnDataField => $selectedColumnNames,
                // Save the array of KEYWORDS selected by the user
                'selected_keywords' => $selectedKeywords,
                'file_path' => $filteredRelativePath,
            ]);
        } catch (\Exception $e) {
            Log::error('FilterUpload DB Saving Error', ['exception' => $e]);
             // Attempt to delete the generated file if DB save fails
            Storage::disk('local')->delete($filteredRelativePath);
            return redirect()->route('filtered.index')->withErrors(['database' => 'Filtered file created, but failed to save record to database. The filtered file has been removed.']);
        }

        // --- Success Redirect ---
        $rowsRemoved = $inputRowCount - $outputRowCount;
        return redirect()->route('filtered.index')->with('success', "File filtered successfully using selected columns/keywords. Processed $inputRowCount rows, removed $rowsRemoved, kept $outputRowCount. Result ready for download.");
    }

    public function processFilter(Request $request)
    {
        $request->validate([
            'original_upload_id' => 'required|exists:original_uploads,id',
            'column' => 'required|array|min:1',
            'column.*' => 'required|string',
            'keywords' => 'required|array|min:1',
            'keywords.*' => 'required|string',
        ]);

        $originalUploadId = $request->input('original_upload_id');
        $selectedColumnNames = $request->input('column');
        $selectedKeywords = $request->input('keywords');

        $keywordsForFiltering = array_filter(
            array_map('strtolower', array_map('trim', $selectedKeywords)),
            function($kw) { return !empty($kw); }
        );

        if (empty($keywordsForFiltering)) {
             return back()->withErrors(['keywords' => 'No valid keywords provided after cleaning.'])->withInput();
        }

        $originalUpload = OriginalUpload::find($originalUploadId);
        if (!$originalUpload || !Storage::disk('local')->exists($originalUpload->file_path)) {
             return redirect()->route('uploads.index')->with('error', 'Original file not found or inaccessible.');
        }

        // --- Filtering Logic (remains the same) ---
        $filteredRecords = [];
        $headerRow = [];
        $inputRowCount = 0;
        $outputRowCount = 0;
        $filterColumnIndices = [];

        try {
            $spreadsheet = IOFactory::load($originalUpload->storagePath);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestDataRow();
            $highestColumn = $sheet->getHighestDataColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

            $headerMap = [];
            for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                 $headerValueRaw = $sheet->getCell([$col, 1])->getValue();
                 $headerValue = $headerValueRaw !== null ? trim((string)$headerValueRaw) : '';
                 $headerRow[] = $headerValue;
                 if(!empty($headerValue)) {
                     $headerMap[strtolower($headerValue)] = $col;
                 }
            }

            $notFoundColumns = [];
            foreach($selectedColumnNames as $colName) {
                $lowerColName = strtolower(trim($colName));
                if (isset($headerMap[$lowerColName])) {
                    $filterColumnIndices[] = $headerMap[$lowerColName];
                } else {
                    $notFoundColumns[] = $colName;
                }
            }

            if (!empty($notFoundColumns)) {
                 return back()->withErrors(['column' => 'The following selected columns were not found: ' . implode(', ', array_map('e', $notFoundColumns))])->withInput();
            }
            if (empty($filterColumnIndices)) {
                 return back()->withErrors(['column' => 'No valid columns to filter were identified.'])->withInput();
             }

            for ($row = 2; $row <= $highestRow; ++$row) {
                $inputRowCount++;
                $rowData = [];
                 for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                    $rowData[] = $sheet->getCell([$col, $row])->getValue();
                 }
                $rowContainsKeyword = false;
                foreach ($filterColumnIndices as $colIndexToCheck) {
                    $cellValueToCheck = $sheet->getCell([$colIndexToCheck, $row])->getValue();
                    $cellValueStringLower = trim(strtolower((string)$cellValueToCheck));
                    if (!empty($cellValueStringLower)) {
                        foreach ($keywordsForFiltering as $keyword) {
                            if (Str::contains($cellValueStringLower, $keyword)) {
                                $rowContainsKeyword = true;
                                break 2;
                            }
                        }
                    }
                }
                if (!$rowContainsKeyword) {
                    $filteredRecords[] = $rowData;
                    $outputRowCount++;
                }
            }
        } catch (\Exception $e) {
            Log::error("Error filtering OriginalUpload ID {$originalUpload->id}: " . $e->getMessage());
            return back()->with('error', 'An error occurred during the filtering process.')->withInput();
        }


        // --- *** UPDATED: Save Filtered File with Correct Format *** ---
        $originalExtension = strtolower(pathinfo($originalUpload->stored_filename, PATHINFO_EXTENSION));
        $baseFilename = pathinfo($originalUpload->stored_filename, PATHINFO_FILENAME);
        $filteredDirectory = 'filtered_files';
        $writer = null; // Initialize writer
        $outputExtension = 'csv'; // Default to CSV

        // Determine the correct writer and output extension
        if ($originalExtension === 'xlsx') {
            $outputExtension = 'xlsx';
        } elseif ($originalExtension === 'xls') {
            // Note: Saving as XLS is possible but less common, often better to output XLSX or CSV
            $outputExtension = 'xls'; // Or force to 'xlsx' or 'csv'
        }
        // else, it remains 'csv'

        // Construct filename with the determined OUTPUT extension
        $filteredFilename = 'filtered_' . $baseFilename . '_' . time() . '.' . $outputExtension;
        $filteredRelativePath = $filteredDirectory . '/' . $filteredFilename;
        $filteredFullPath = storage_path('app/' . $filteredRelativePath);

        Storage::disk('local')->makeDirectory($filteredDirectory);

        try {
            // Create a new Spreadsheet object to write the filtered data
            $writerSpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $writerSheet = $writerSpreadsheet->getActiveSheet();
            $writerSheet->fromArray($headerRow, NULL, 'A1');      // Write header
            $writerSheet->fromArray($filteredRecords, NULL, 'A2'); // Write filtered data rows

            // --- Choose the correct WRITER based on the desired output format ---
            if ($outputExtension === 'xlsx') {
                 Log::info("Saving filtered file as XLSX: " . $filteredFilename);
                 $writer = new XlsxWriter($writerSpreadsheet); // Use XLSX Writer
            }
            // Add 'xls' writer if you decided to support saving as XLS
            // elseif ($outputExtension === 'xls') {
            //     Log::info("Saving filtered file as XLS: " . $filteredFilename);
            //     $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($writerSpreadsheet); // Use XLS Writer
            // }
             else { // Default to CSV writer
                 Log::info("Saving filtered file as CSV: " . $filteredFilename);
                 $writer = new CsvWriter($writerSpreadsheet);  // Use CSV Writer
                 $writer->setUseBOM(true);                     // Set BOM for CSV
                 // Optional: Set CSV delimiter/enclosure if needed
                 // $writer->setDelimiter(',');
                 // $writer->setEnclosure('"');
            }

            // Save the file using the selected writer
            $writer->save($filteredFullPath);

        } catch (\Exception $e) {
             Log::error('Filtered File Saving Error', ['filename' => $filteredFullPath, 'exception' => $e]);
             return redirect()->route('filtered.index')->withErrors(['file' => 'Could not save the filtered file: ' . $e->getMessage()]);
        }
        // --- End Updated File Saving ---


        // --- Create FilterUpload Record (remains the same) ---
        try {
            $columnDataField = 'filtered_column'; // Or 'filtered_columns'
            FilterUpload::create([
                'original_upload_id' => $originalUpload->id,
                $columnDataField => $selectedColumnNames,
                'selected_keywords' => $selectedKeywords,
                'file_path' => $filteredRelativePath, // Path to the new filtered file
            ]);
        } catch (\Exception $e) {
            Log::error('FilterUpload DB Saving Error', ['exception' => $e]);
            Storage::disk('local')->delete($filteredRelativePath);
            return redirect()->route('filtered.index')->withErrors(['database' => 'Filtered file created, but failed to save record. File removed.']);
        }

        // --- Success Redirect ---
        $rowsRemoved = $inputRowCount - $outputRowCount;
        return redirect()->route('filtered.index')->with('success', "File filtered successfully. Processed $inputRowCount rows, removed $rowsRemoved, kept $outputRowCount. Result saved as .$outputExtension"); // Indicate output format
    }
    // List Filtered Results
    public function indexFiltered()
    {
        // Eager load the original upload relationship to display its name
        $filteredFiles = FilterUpload::with('originalUpload')
                                     ->latest()
                                     ->paginate(15);
        return view('filtered.index', compact('filteredFiles'));
    }

    // Download Filtered File
    public function downloadFiltered1(FilterUpload $filterUpload)
    {
        if (!Storage::disk('local')->exists($filterUpload->file_path)) {
             return back()->with('error', 'Filtered file not found.');
        }
        $originalExtension = pathinfo($filterUpload->originalUpload->original_filename , PATHINFO_EXTENSION);
        $downloadName = pathinfo($filterUpload->originalUpload->original_filename ?? 'download', PATHINFO_FILENAME)
                      . '_filtered_' . time()  . '.' . $originalExtension; 
        return Storage::disk('local')->download($filterUpload->file_path, $downloadName);
    }

    public function downloadFiltered(FilterUpload $filterUpload)
     {
         $filePath = $filterUpload->file_path; // e.g., 'filtered_files/filtered_abc_123.xlsx'

         if (!Storage::disk('local')->exists($filePath)) {
              return back()->with('error', 'Filtered file not found.');
         }

         // Determine the actual extension of the SAVED filtered file
         $savedExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

         // Create a user-friendly download name (e.g., original_name_filtered.ext)
         $baseDownloadName = pathinfo($filterUpload->originalUpload->original_filename ?? 'filtered_download', PATHINFO_FILENAME);
         $downloadName = $baseDownloadName . '_filtered_' . $filterUpload->id . '.' . $savedExtension; // Use the ACTUAL extension

         // --- Determine the correct Content-Type header ---
         $mimeType = 'application/octet-stream'; // Default binary type
         if ($savedExtension === 'xlsx') {
             $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
         } elseif ($savedExtension === 'xls') {
             $mimeType = 'application/vnd.ms-excel';
         } elseif ($savedExtension === 'csv') {
             $mimeType = 'text/csv';
         }

         // Set headers for the download response
         $headers = [
             'Content-Type' => $mimeType,
             // Optional: Can add Content-Disposition for inline/attachment, but download() handles it well
             // 'Content-Disposition' => 'attachment; filename="' . $downloadName . '"',
         ];

         Log::info("Downloading filtered file: {$filePath} as {$downloadName} with Content-Type: {$mimeType}");

         // Return the download response with correct headers
         return Storage::disk('local')->download($filePath, $downloadName, $headers);
     }

    // Delete Filtered Result and File
    public function destroyFiltered(FilterUpload $filterUpload)
    {
        // Add authorization check
         if (auth()->user()->role !== 'admin') { // Simple example
             return redirect()->route('filtered.index')->with('error', 'Unauthorized action.');
         }

         try {
     
             $filterUpload->delete();
            return redirect()->route('filtered.index')->with('success', 'Filtered result deleted successfully.');
        } catch (\Exception $e) {
            Log::error("Error deleting filtered result ID {$filterUpload->id}: " . $e->getMessage());
            return redirect()->route('filtered.index')->with('error', 'Could not delete the filtered result.');
        }
    }
}
