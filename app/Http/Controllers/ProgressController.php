<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DownloadingProgressViewService;
use Illuminate\Support\Facades\Storage;

class ProgressController extends Controller
{
    public function checkUpload($pid) {
        if (DownloadingProgressViewService::checkCompleted($pid) == True) {
            DownloadingProgressViewService::delete($pid);

            return response("https://s3.twcstorage.ru/05574539-988f78c4-7e6e-46de-a427-87fb91715e4b/uploads/$pid/master.m3u8", 200);
        }

        return response(DownloadingProgressViewService::getProgress($pid), 200);
    }
}
