<?php
namespace Labspace\UploadApi\Services\Image;

use Carbon\Carbon;
use Labspace\UploadApi\Services\ImageUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Image;
use Exception;
use Labspace\UploadApi\Services\Image\ImageInterface;

class UrlSource implements ImageInterface
{
    public $filename=null;
    protected $url;

    public function __construct($url,$filename=null)
    {
        $this->url = $url;
        //取得檔案
        if(!$filename){
            $path_parts = pathinfo($url);
            $this->filename = date('YmdHis').get_millisecond(). '.' .$path_parts['extension']; 
        } else {
            $this->filename = $filename;
        }
    }

    /**
    * 儲存圖片
     * @param $file_path 儲存絕對路徑
     * @param $show_path 回傳顯示的相對路徑
     * @param $width 寬度
     * @return string
    */
    public function save($file_path,$show_path,$width)
    {

        $img = Image::make($this->url);
        $img->resize($width, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        //檔案處理
        if($img->save($file_path)){
            return $show_path.$this->filename;
        } else {
            return '';
        }

    }

     /**
    * 儲存縮圖
     * @param $file_path 儲存絕對路徑
     * @param $show_path 回傳顯示的相對路徑
     * @param $width 寬度
     * @return string
    */
    public function saveThumbnail($file_path)
    {        
        //檔案處理
        $img = Image::make($this->url);
        $img->resize(config('labspace-upload-api.thumbnail_width'), null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img->save($file_path);

    }

}