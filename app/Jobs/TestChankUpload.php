<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\FileHandlerService;

class TestChankUpload implements ShouldQueue
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

    public function __construct($chunkPath, $videoId, $chunkNumber)
    {
        $this->chunkPath = $chunkPath;
        $this->videoId = $videoId;
        $this->chunkNumber = $chunkNumber;
        $this->service = new FileHandlerService();
    }

    public function handle()
    {
        foreach ($this->resolutions as $res => $params) {
            $this->convertToHls($res, $params);
            $this->updateMasterPlaylist($res);
        }
    }

    protected function convertToHls($resolution, $params)
    {
        $segmentPattern = "video_files/{$this->videoId}/{$resolution}/{$this->videoId}_%05d.ts";
        $playlistPath = "video_files/{$this->videoId}/{$resolution}/{$this->videoId}.m3u8";
        // $this->service->convertToHLS($params['width'], $params['height'], $params['bitrate'], $segmentPattern, $playlistPath);
        $this->service->convertToHLSOnes($this->videoId, $this->chunkPath, $resolution, $params['width'], $params['height'], $params['bitrate']);
    }

    protected function updateMasterPlaylist($resolution)
    {
        $masterPath = storage_path("app/public/video_files/{$this->videoId}/master.m3u8");
        
        // Создаем мастер-плейлист если его нет
        if (!file_exists($masterPath)) {
            file_put_contents($masterPath, "#EXTM3U\n#EXT-X-VERSION:3\n");
        }

        // Добавляем запись для разрешения (если еще не добавлено)
        $playlistContent = file_get_contents($masterPath);
        $streamInfo = "#EXT-X-STREAM-INF:BANDWIDTH={$this->resolutions[$resolution]['bitrate']}000,RESOLUTION={$this->resolutions[$resolution]['width']}x{$this->resolutions[$resolution]['height']}\n{$resolution}/playlist.m3u8\n";
        
        if (!str_contains($playlistContent, $streamInfo)) {
            file_put_contents($masterPath, $streamInfo, FILE_APPEND);
        }

        // Обновляем плейлист для конкретного разрешения
        $this->updateResolutionPlaylist($resolution);
    }

    protected function updateResolutionPlaylist($resolution)
    {
        $playlistPath = storage_path("app/public/video_files/{$this->videoId}/{$resolution}/playlist.m3u8");
        $newEntry = "#EXTINF:4.000000,\nchunk_{$this->chunkNumber}_000.ts\n";
        
        if (!file_exists($playlistPath)) {
            file_put_contents($playlistPath, "#EXTM3U\n#EXT-X-VERSION:3\n#EXT-X-TARGETDURATION:4\n#EXT-X-MEDIA-SEQUENCE:0\n");
        }
        
        file_put_contents($playlistPath, $newEntry, FILE_APPEND);
    }
}
