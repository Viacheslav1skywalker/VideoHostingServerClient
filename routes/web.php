<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\ProgressController;


Route::get('/upload', function () {
  return view('file-upload');
});

Route::get('/upload-video', function () {
  return view('upload');
});

Route::post('/upload', [FileUploadController::class, 'upload']);
Route::post('/startStream'); 
Route::post('/stopStream');
Route::get('/process_video', [FileUploadController::class, 'testUpload']);
// Route::get('/process_video2', [FileUploadController::class, 'chankUploadPsevdocode']);

Route::get('/testSplicing', [FileUploadController::class, 'uploadPsevdoCode']);
Route::post('check-progress/{id}', [ProgressController::class, 'checkUpload']);

