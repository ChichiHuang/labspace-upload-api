

母專案要做的事情

STEP.0 

圖片處理intevention先安裝設定好、jwt機制要ready~ cors也要準備好

http://image.intervention.io/

============

STEP.1

安裝套件

composer require labspace/upload-api


============


STEP.2

到config/app.php 的providers加上

Labspace\UploadApi\UploadApiServiceProvider::class,

============

STEP.3

在app\App\Providers\AppServiceProvider 的boot 裡面新增base64圖片檢查code

（記得use Validator）

Validator::extend('base64image', function ($attribute, $value, $parameters, $validator) {
            $explode = explode(',', $value);
            $allow = ['png', 'jpg', 'svg'];
            $format = str_replace(
                [
                    'data:image/',
                    ';',
                    'base64',
                ],
                [
                    '', '', '',
                ],
                $explode[0]
            );
            // check file format
            if (!in_array($format, $allow)) {
                return false;
            }
            // check base64 format
            if (!preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $explode[1])) {
                return false;
            }
            return true;
        });


============

STEP.4

php artisan vendor:publish --tag=config

 會新增專屬config檔
 labspace-upload-api.php
 裡面可以設定登入user model的位置


====================

使用說明


[上傳圖片（file） - POST]
file:檔案（必填）
width:寬度(必填)
type:類型
token:使用者token

http://[server_url]/lab/api/upload/image/file

{
    "status": true,
    "data": {
        "filepath": "/avatar/1/20200121182207182207.jpg"
    },
    "success_code": "SUCCESS"
}




[上傳圖片（base64） - POST]
file:base64字串（必填）
width:寬度(必填)
type:類型
token:使用者token
extension:檔案類型

http://[server_url]/lab/api/upload/image/base64

{
    "status": true,
    "data": {
        "filepath": "/avatar/1/20200121182207182207.jpg"
    },
    "success_code": "SUCCESS"
}





[上傳影片（file） - POST]
file:檔案（必填）
type:類型
token:使用者token

http://[server_url]/lab/api/upload/video/file

{
    "status": true,
    "data": {
        "filepath": "/avatar/1/20200121182207182207.jpg"
    },
    "success_code": "SUCCESS"
}