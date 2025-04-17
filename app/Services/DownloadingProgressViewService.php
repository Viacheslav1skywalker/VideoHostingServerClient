<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Models\ProgressDownloading;

class DownloadingProgressViewService 
{
    public static function create(string $pid) {
        ProgressDownloading::create([
            'pid' => $pid,
            'progress' => 0,
        ]);
    }

    public static function getProgress(string $pid) {
        // добавить проверку на существование
        $item = ProgressDownloading::find($pid);
        if (!$item) {

            return 0;
        }

        return $item->progress;
    }

    public static function increment(string $pid, int $value = 10) {
        // добавить проверку на существование
        $item = ProgressDownloading::find($pid);
        $nowProgress = $item->progress;
        $item->update(['progress' => $nowProgress + $value]);
    }

    public static function delete(string $pid) {
        ProgressDownloading::find($pid)->delete();
    }

    public static function checkCompleted(string $pid) {
        $title = ProgressDownloading::find($pid);

        if (!$title) {

            return False;
        }

        if ($title->is_completed == True) {

            return True;
        }

        return False;
    } 

    public static function doComleted(string $pid) {
        $item = ProgressDownloading::find($pid);
        $item->update(['is_completed' => True]);
    }
}