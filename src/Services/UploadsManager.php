<?php
namespace Labspace\UploadApi\Services;

use Carbon\Carbon;
use Dflydev\ApacheMimeTypes\PhpRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Image;
use Exception;

class UploadsManager
{
    
    protected $root_path;
    
    public function __construct()
    {
        $this->root_path = storage_path('app/public/');
    }
    
    /**
    * 資料夾位置
    */
    protected function setFolder($save_path)
    {
        $folder = $this->root_path.$save_path;
        if(!File::exists($folder)) {
            File::makeDirectory($folder,  0755, true);
            if(!File::exists($folder.'thumbs/')) {
                File::makeDirectory($folder.'thumbs/',  0755, true);
            }
        }
        return $this->root_path.$save_path;
    }

    /**
    * 刪除檔案
    */
    public function delete($path)
    {
        $file = $this->root_path.$path;
        if(File::exists($file)) {
            return unlink($file);
        }
        return false;
        
    }
    
    /**
    * 檢查檔案類型
    */
    public function checkFileType($type_arr,Request $request)
    {
        $mime = Input::file('file')->getMimeType();
        foreach($type_arr as $type){
            if (preg_match("/".$type."/i", $mime)) {
                return true;
            }
        }
        return false;
    }


}