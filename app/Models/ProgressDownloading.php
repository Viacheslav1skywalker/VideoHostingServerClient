<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressDownloading extends Model
{
    protected $table = 'downloading_progress'; 
    protected $primaryKey = 'pid'; 
    public $incrementing = false;  
    protected $keyType = 'string'; 
    protected $fillable = ['pid', 'progress', 'is_completed'];
    protected $casts = ['progress' => 'integer'];
}
