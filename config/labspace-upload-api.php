<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Labspace upload api 設定
    |--------------------------------------------------------------------------
    |
    | Most templating systems load templates from disk. Here you may specify
    | an array of paths that should be checked for your views. Of course
    | the usual Laravel view path has already been registered for you.
    |
    */

    'user_model' => 'App\Models\User', //user model 位置

    'max_size' => 200000, //開放的檔案上傳size (KB)

    'folder_names' => 'avatar,ckeditor', //開放的檔案上傳的資料夾名稱(逗號隔開)

    'file_types' => [
        'image' => 'jpeg,png,jpg,svg',
        'video' => 'mp4'
    ], //開放的檔案上傳的檔案類型(逗號隔開)

    'thumbnail_width' => 200,
    'thumbnail_sizes' => [
        'b' => 1200,
        'm' => 800,
        's' => 600
    ],
    'file-system-driver' =>env('FILESYSTEM_DRIVER','file-manager'),
    //圖片影片
    'valid_mime'   => [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'svg',
        'mp4'
    ],
    //多格式檔案直接上傳類型
    'file_valid_mime' => [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'svg',
        'mp4',
        'doc',
        'pdf',
        'docx',
        'xls',
        'xlsx',
        'txt',
        'zip',

    ],

    'upload_url' => env('UPLOAD_URL'),

];
