<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Symfony\Component\Process\Process;
use App\Services\FileHandlerService;


class ChankProcessVideo implements ShouldQueue
{
    use Queueable;

    public $chunkPath;
    public $videoId;
    public $chunkNumber;
    public $resolutions = [
        '1080p' => ['width' => 1920, 'height' => 1080, 'bitrate' => 6000],
        '720p'  => ['width' => 1280, 'height' => 720,  'bitrate' => 3000],
        '480p'  => ['width' => 854,  'height' => 480,  'bitrate' => 1500]
    ];
    public $service;
    public string $sourceFilePath;

    public function __construct($sourceFilePath, $videoId, $chunkNumber)
    {
        $this->sourceFilePath = $sourceFilePath;
        $this->videoId = $videoId;
        $this->chunkNumber = $chunkNumber;
        $this->service = new FileHandlerService();
    }

    public function handle()
    {
        $this->service->convertToHLS($this->videoId, $this->sourceFilePath);
    }

   
}