<?php
namespace Labspace\UploadApi\Services\Image;

use Illuminate\Http\Request;
use Exception;
use Labspace\UploadApi\Services\Image\ImageInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Image;
use Storage;

class FileSource implements ImageInterface
{
    public $filename=null;
    protected $image;

    public function __construct(Request $request,$filename=null,$request_name='file')
    {
        $this->image = Input::file($request_name);
        //取得檔案
        if(!$filename){
            $this->filename = date('Ymd').date('His'). '.' .$this->image->getClientOriginalExtension(); 
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
    public function save($file_path,$show_path,$width=0)
    {   

        $disk = Storage::disk(config('labspace-upload-api.file-system-driver')); 
        //檔案處理
        $img = Image::make($this->image->getRealPath());
        $img->resize($width, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        
        $image_normal = $img->stream();
        if($disk->put($show_path.$this->filename, $image_normal->__toString())){
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
        /*$img = Image::make($this->image->getRealPath());
        $img->resize(config('labspace-upload-api.thumbnail_width'), null, function ($constraint) {
            $constraint->aspectRatio();
        });*/

    }
    

}