<?php

namespace App\Services;

use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Illuminate\Support\Facades\Storage;

class FileHandlerService 
{
    private const RESOLUTIONS = [
        '2160p' => [3840, 2160, 15000], // 4K UHD (15000 kbps)
        '1440p' => [2560, 1440, 8000],  // 2K QHD (8000 kbps)
        '1080p' => [1920, 1080, 5000],  // Full HD (5000 kbps)
        '720p'  => [1280, 720, 2500],   // HD (2500 kbps)
        '480p'  => [854, 480, 1000],    // SD (1000 kbps)
        '360p'  => [640, 360, 800],     // Mobile HD (800 kbps)
        '240p'  => [426, 240, 400],     // Mobile SD (400 kbps)
        '144p'  => [256, 144, 200],     // Low quality (200 kbps)
    ];

    public function convertToHLS(string $videoId, string $inputPath)
    {
        if (!Storage::disk('public')->exists($inputPath)) {
            throw new \Exception("File not found: " . $inputPath);
        }

        $outputDir = "uploads/{$videoId}/hls";
        Storage::disk('public')->makeDirectory($outputDir);

        $export = FFMpeg::fromDisk('public')
            ->open($inputPath)
            ->exportForHLS();

        foreach (self::RESOLUTIONS as $resolution => [$width, $height, $bitrate]) {
            $segmentPattern = "{$outputDir}/{$resolution}-{$videoId}_%05d.ts";
            $playlistPath = "{$outputDir}/{$resolution}-{$videoId}.m3u8";

            $export->addFormat($this->getX264Format(
                $width,
                $height,
                $bitrate,
                $segmentPattern,
                $playlistPath
            ));

            DownloadingProgressViewService::increment($videoId);
        }

        $masterPlaylistPath = "{$outputDir}/master.m3u8";
        $export->save($masterPlaylistPath);

        $this->generateMasterPlaylist($videoId);

        return [
            'master_playlist' => $masterPlaylistPath,
            'resolutions' => array_keys(self::RESOLUTIONS)
        ];
    }
    
    private function getX264Format(
        int $width,
        int $height,
        int $bitrate,
        string $segmentPattern,
        string $playlistPath
    ): \FFMpeg\Format\Video\X264 {
        $width = round($width / 2, 0) * 2;
        $height = round($height / 2, 0) * 2;

        return (new \FFMpeg\Format\Video\X264('aac'))
            ->setKiloBitrate($bitrate)
            ->setAudioChannels(2)
            ->setAudioKiloBitrate(128)
            ->setAdditionalParameters([
                '-vf', "scale=w={$width}:h={$height}",
                '-preset', 'fast',
                '-hls_time', '5',
                '-hls_list_size', '0',
                '-hls_segment_filename', $segmentPattern,
                '-hls_playlist_type', 'vod',
            ]);
    }

    private function generateMasterPlaylist(string $videoId): void
    {
        $outputDir = "video_files/{$videoId}";
        $playlistContent = "#EXTM3U\n";
        
        foreach (self::RESOLUTIONS as $resolution => [$width, $height, $bitrate]) {
            $playlistContent .= "#EXT-X-STREAM-INF:BANDWIDTH={$bitrate}000,RESOLUTION={$width}x{$height}\n";
            $playlistContent .= "{$resolution}-{$videoId}.m3u8\n";
        }

        Storage::disk('public')->put(
            "{$outputDir}/master.m3u8",
            $playlistContent
        );
    }
}