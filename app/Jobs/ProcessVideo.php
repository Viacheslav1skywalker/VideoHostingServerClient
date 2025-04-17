<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\FileHandlerService;

class ProcessVideo implements ShouldQueue
{
    use Queueable;

    public string $videoPath;

    public string $videoId;

    public $timeout = 3600; // 1 час в секундах

    /**
     * Create a new job instance.
     */
    public function __construct(string $videoPath, string $videoId)
    {
        $this->videoPath = $videoPath;
        $this->videoId = $videoId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $fileHandler = new FileHandlerService(); // TODO: добавить в сервис провайдеры или сделать методы статическими
        $fileHandler->convertToHLS($this->videoId, $this->videoPath);
        MoveToS3Storage::dispatch("uploads/$this->videoId/hls", $this->videoId)
            ->onQueue('move-to-s3-worker');
    }
}
