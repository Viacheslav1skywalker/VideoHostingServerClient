<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\S3Service;

class MoveToS3Storage implements ShouldQueue
{
    use Queueable;

    public $service;

    public $dirPath;

    public $fileId;

    /**
     * Create a new job instance.
     */
    public function __construct($dirPath, $fileId)
    {
        $this->service = new S3Service();
        $this->dirPath = $dirPath;
        $this->fileId = $fileId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->service->uploadFilesFromDir($this->dirPath, $this->fileId);
    }
}
