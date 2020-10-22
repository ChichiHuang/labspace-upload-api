<?php
namespace Labspace\UploadApi\Services\Video;

use Illuminate\Http\Request;
use Exception;
use Labspace\UploadApi\Services\Video\VideoInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Log;
use Storage;

class FileSource implements VideoInterface
{
    public $filename=null;
    protected $video;

    public function __construct(Request $request,$filename=null,$request_name='file')
    {
        
        $this->video = Input::file($request_name);
        //取得檔案
        if(!$filename){
            $this->filename = date('YmdHis').date('His'). '.' .$this->video->getClientOriginalExtension(); 
        } else {
            $this->filename = $filename;
        }
    }

    /**
    * 儲存
     * @param $file_path 儲存絕對路徑
     * @param $show_path 回傳顯示的相對路徑
     * @return string
    */
    public function save($file_path,$show_path)
    {     
        $disk = Storage::disk(config('labspace-upload-api.file-system-driver')); 
    
        if( $disk->put($show_path.$this->filename, file_get_contents($this->video))){
            return $show_path.$this->filename;
        } else {
            return '';
        }
    }

    

}