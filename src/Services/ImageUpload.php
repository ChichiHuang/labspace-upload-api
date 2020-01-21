<?php
namespace Labspace\UploadApi\Services;

use Carbon\Carbon;
use Labspace\UploadApi\Services\UploadsManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Image;
use Exception;
use App\Services\Uploads\Image\ImageInterface;

class ImageUpload extends UploadsManager
{
    protected $width = 400;//預設寬度
    protected $file_path; 
    protected $show_path; 
    protected $imageSource;
    protected $folder_path;

    public function __construct( $imageSource)
    {
        parent::__construct();
        $this->imageSource = $imageSource;
    
    }

    public function setImageInfo($width,$show_path){
        if($width > 0){
            $this->width = $width;
        } 
 
        //資料夾位置
        $folder = $this->setFolder($show_path);
        $this->show_path = $show_path;
        $this->folder_path = $folder;
        //檔案處理
        $this->file_path = $folder.$this->imageSource->filename;
    }

    public function saveImage(){
        $show_path = $this->imageSource->save($this->file_path,$this->show_path,$this->width);
        $this->imageSource->saveThumbnail($this->folder_path.'thumbs/'.$this->imageSource->filename ,$this->file_path);
        return $show_path ;
    }
    
    
    

}