<?php
namespace Labspace\UploadApi\Services\Image;

use Illuminate\Http\Request;
use Exception;
use Labspace\UploadApi\Services\Image\ImageInterface;
use Image;

class Base64Source implements ImageInterface
{
    public $filename=null;
    protected $url;
    protected $request;

    public function __construct(Request $request,$filename=null)
    {
        $this->request = $request;
        //取得檔案
        if(!$filename){
            $this->filename = date('Ymd').date('His'). '.' .$request->extension; 
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
        $file = str_replace('data:image/'.$this->request->extension.';base64,', '', $this->request->file);//處理接收的參數
        $decoded = base64_decode($file);//解碼

        if(file_put_contents($file_path, $decoded)){
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
    public function saveThumbnail($file_path,$origin_file_path)
    {        
        //檔案處理
        $img = Image::make($origin_file_path);
        $img->resize(config('labspace-upload-api.thumbnail_width'), null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $img->save($file_path);

    }

}