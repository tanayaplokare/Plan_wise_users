<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UploadFileController;
use App\Http\Controllers\UploadController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


Route::get('forgot-password', [DashboardController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('forgot-password', [DashboardController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('reset-password/{token}', [DashboardController::class, 'showResetForm'])->name('password.reset');
Route::post('reset-password', [DashboardController::class, 'updatePassword'])->name('password.update');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('plans', PlanController::class);
    Route::resource('users', UserController::class);
    Route::resource('keywords', KeywordController::class);
    Route::resource('columns', ColumnController::class);
    Route::resource('settings', SettingController::class);

    
    Route::get('/planuploads', [UploadFileController::class, 'index'])->name('planuploads.index');
    Route::get('/planuploads/create', [UploadFileController::class, 'create'])->name('planuploads.create');
    Route::post('/planuploads', [UploadFileController::class, 'store'])->name('planuploads.store');
    Route::get('/planuploads/download/{id}', [UploadFileController::class, 'download'])->name('planuploads.download');
    Route::delete('/uploads/{id}', [UploadFileController::class, 'destroy'])->name('planuploads.destroy');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/uploadForm', [SettingController::class, 'uploadForm'])->name('setting.upload_form');
    Route::post('/upload_excel', [SettingController::class, 'uploadFile'])->name('upload.file');

    Route::post('/process-filter', [SettingController::class, 'processFilter'])->name('process.filter');
    Route::get('/filtered-uploads', [SettingController::class, 'filteredUploadsList'])->name('filtered.uploads.list');
    Route::get('/download-filtered/{filterUpload}', [SettingController::class, 'downloadFilteredFile'])->name('download.filtered.file');

    Route::delete('/filtered-uploads/{filterUpload}', [SettingController::class, 'destroy'])
    ->name('filtered.uploads.destroy')
    ->middleware(['auth']);

    //--- Original File Uploads ---
    Route::get('/uploads', [UploadController::class, 'indexOriginal'])->name('uploads.index');
    Route::get('/uploads/create', [UploadController::class, 'createOriginal'])->name('uploads.create');
    Route::post('/uploads', [UploadController::class, 'storeOriginal'])->name('uploads.store');
    Route::delete('/uploads/{originalUpload}', [UploadController::class, 'destroyOriginal'])->name('uploads.destroy');
      //--- Filtering Process ---
      Route::get('/uploads/{originalUpload}/filter', [FilterController::class, 'showFilterForm'])->name('uploads.filter.form'); // Show form to start filtering
      Route::post('/filter', [FilterController::class, 'processFilter'])->name('filter.process'); // Process the filtering
  
      // --- Filtered Results ---
      Route::get('/filtered-results', [FilterController::class, 'indexFiltered'])->name('filtered.index');
      Route::get('/filtered-results/{filterUpload}/download', [FilterController::class, 'downloadFiltered'])->name('filtered.download');
      Route::delete('/filtered-results/{filterUpload}', [FilterController::class, 'destroyFiltered'])->name('filtered.destroy');
  
});