<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use App\Services\DownloadingProgressViewService;

class FileSplicing implements ShouldQueue
{
    use Queueable;

    public string $chuncPath;

    public string $fileExt;

    public int $countChuncks;

    public string $fileId;

    public $timeout = 3600; // 1 час в секундах

    /**
     * Create a new job instance.
     */
    public function __construct(string $chuncPath, string $fileExt, int $countChuncks, string $fileId)
    {
        $this->chuncPath = $chuncPath;
        $this->fileExt = $fileExt;
        $this->countChuncks = $countChuncks;
        $this->fileId = $fileId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $derictory = storage_path("app/public/uploads/$this->fileId");
        $filePath = "{$derictory}/output.mp4";
        if (!File::exists($derictory)) {
            throw new \Exception('derictiry not exists');
        }
        $handle = fopen($filePath, 'wb');
        
        for ($i = 1; $i <= $this->countChuncks; $i++) {

            $chankPath = "{$derictory}/$i.bin";
            $chunkContent = file_get_contents($chankPath);
            fwrite($handle, $chunkContent);
        }

        fclose($handle);

        // отдаем путь к файлу в другую очередь по преобразованию файла в формат m3u8
        ProcessVideo::dispatch("uploads/$this->fileId/output.mp4", $this->fileId)
            ->onQueue('video-processing');

        // TODO: продумать очередь по удалению не нужных бинарных файлов, или вынести логику данной job 
        // и делать это там
        // TODO: продумать логику восстановления обработки данных (например после сбоя), писать данные во что
        // то типо wal файлов

        DownloadingProgressViewService::create($this->fileId);
        DownloadingProgressViewService::increment($this->fileId);
    }
}
