<?php
namespace Labspace\UploadApi\Exceptions;

use Exception;

class FileTypeInvalidException extends Exception
{
    protected $message = '檔案格式錯誤或大小超過限制@FILE_TYPE_INVALID';
    protected $code = '403';


}
