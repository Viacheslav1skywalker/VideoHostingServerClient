<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class S3Service 
{
    private $s3Directory = 'uploads'; // Директория в S3

    public function uploadFilesFromDir(string $dirPath, string $fileId) {
        $files = File::files(storage_path("app/public/uploads/$fileId/hls"));

        foreach ($files as $file) {
            $filePath = $file->getPathname();
            $fileName = $file->getFilename();
            
            Storage::disk('tws3')->putFileAs(
                "uploads/$fileId", 
                $filePath,    
                $fileName
            );
        }

        DownloadingProgressViewService::increment($fileId);

        // TODO: вынести в воркер (нарушение принципа единой ответственности)
        DownloadingProgressViewService::doComleted($fileId);
    }
}