<?php
namespace Labspace\UploadApi\Services;

use Carbon\Carbon;
use Labspace\UploadApi\Services\UploadsManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Exception;
use Labspace\UploadApi\Services\Video\VideoInterface;

class VideoUpload extends UploadsManager
{
    protected $width = 800;//預設寬度
    protected $file_path; 
    protected $show_path; 
    protected $videoSource;

    public function __construct($videoSource)
    {
        parent::__construct();
        $this->videoSource = $videoSource;
    
    }

    public function setFileInfo($show_path){

        //資料夾位置
        $folder = $this->setFolder($show_path);
        $this->show_path = $show_path;
        
        //檔案處理
        $this->file_path = $folder;
    }

    public function saveFile(){
        return $this->videoSource->save($this->file_path,$this->show_path);
    }
    
    
    

}