<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessVideo;
use Illuminate\Support\Str;
use App\Services\FileHandlerService;
use App\Jobs\FileSplicing;
use App\Jobs\TestChankUpload;
use Exception;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Illuminate\Support\Facades\File;
use Aws\S3\S3Client;
use Mockery\CountValidator\Exact;
use App\Jobs\MoveToS3Storage;

class FileUploadController extends Controller {
    public function uploadPsevdoCode(Request $request)
    {
        
        // проверка файла на корректность и безопасность
        // создание файла и выгрузка файла в локальное хранилище
        // загрузка мета информации о файле с доп инфой для возобновления в дальнейшем
        
        // или объеденение файла в один или -
        // помещаем задачу в очередь или отдаем на выполнение другому сервису
        $fileExt = 'avi';
        $countChuncks = 15;
        $fileId = '1744781525543';
        $derictory = storage_path("app/public/uploads/$fileId");
        $filePath = "{$derictory}/output.{$fileExt}";
        if (!File::exists($derictory)) {
            throw new Exception('derictiry not exists');
        }
        $handle = fopen($filePath, 'wb');
        
        for ($i = 1; $i <= $countChuncks; $i++) {

            $chankPath = "{$derictory}/$i.bin";
            $chunkContent = file_get_contents($chankPath);
            fwrite($handle, $chunkContent);
        }

        fclose($handle);

        return response()->json(['status' => 200]);
    }

    public function upload(Request $request) {
        // TODO: вынести бизнес логику в отдельный модуль и вынести валидацию в middleware
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'No file uploaded'], 400);
        }
        
        $chunk = $request->file('file');
        $fileId = $request->input('fileId');
        $chunkNumber = $request->input('chunkNumber');
        $totalChunks = $request->input('totalChunks');
        $chunkNumber = $request->input('number_chunk');
        $fileExtension = $request->input('fileExtension');

        $filePath = $chunk->storeAs("uploads/$fileId", "$chunkNumber.bin", 'public'); 

        if ($totalChunks == $chunkNumber) {
            FileSplicing::dispatch("uploads/$fileId", $fileExtension, $totalChunks, $fileId)
                ->onQueue('video-splicing');
        }
        
        return response()->json(['file_path' => $filePath]);
    }
    
    private function mergeChunks($fileId, $originalName) {
        $finalPath = "uploads/videos/" . now()->format('Y-m-d');
        $finalName = $fileId . '_' . $originalName;
        
        Storage::makeDirectory($finalPath);
        $output = Storage::disk('local')->path("{$finalPath}/{$finalName}");
    
        for ($i = 1; $i <= Storage::files("chunks/{$fileId}"); $i++) {
            $chunkPath = Storage::path("chunks/{$fileId}/chunk_{$i}");
            file_put_contents($output, file_get_contents($chunkPath), FILE_APPEND);
            unlink($chunkPath);
        }
    
        Storage::deleteDirectory("chunks/{$fileId}");
    }

    public function testUpload(Request $request)
    {
        $videoId = '1744812786506';
        $f = MoveToS3Storage::dispatch("uploads/videoId/hls", $videoId)
            ->onQueue('move-to-s3-worker');
        $this->chankUploadPsevdocode();

        if ($f == false) {
            throw new Exception('error');
        }
        // // $allFiles = Storage::disk('s3')->allFiles('/');

        // // dd($allFiles);
        // $localPath = storage_path('app/public/uploads/1744795963623/котик.mp4');
        // if (!File::exists($localPath)) {
        //     throw new \Exception("Файл не найден: {$localPath}");
        // }
        // // ld();
        // $f = Storage::disk('tws3')->putFileAs(
        //         'vodeos/',
        //         $localPath,
        //         'котик.mp4'
        //     );
   
        // if ($f === false) {
        //     throw new \Exception("ошибка");
        // }
        // ld();
       
    }

    function chankUploadPsevdocode() {
        $files = File::files(storage_path("app/public/uploads/1744811271347/hls"));

        foreach ($files as $file) {
            $filePath = $file->getPathname();
            $fileName = $file->getFilename();
            // dd($filePath);
            $f = Storage::disk('tws3')->putFileAs(
                "uploads/1744811271347/", 
                $filePath,    
                $fileName
            );

            if ($f == false) {
                throw new Exception('error');
            }
        }
    }

}
