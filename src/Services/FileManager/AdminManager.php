<?php
namespace Labspace\UploadApi\Services\FileManager;

use Carbon\Carbon;
use Dflydev\ApacheMimeTypes\PhpRepository;
use Illuminate\Support\Facades\Storage;
use Exception;
use Log;
use Image;

class AdminManager
{
  protected $disk;
  protected $mimeDetect;
  protected $thumb_folders = ['thumbs','thumbs_s','thumbs_m','thumbs_b'];
  protected $thumb_sizes = [];

  public function __construct(PhpRepository $mimeDetect)
  {
    $this->disk = Storage::disk(config('labspace-upload-api.file-system-driver'));
    $this->mimeDetect = $mimeDetect;
    $this->thumb_sizes = config('labspace-upload-api.thumbnail_sizes');
  }

  /**
   * Return files and directories within a folder
   *
   * @param string $folder
   * @return array of [
   *    'folder' => 'path to current folder',
   *    'folderName' => 'name of just current folder',
   *    'breadCrumbs' => breadcrumb array of [ $path => $foldername ]
   *    'folders' => array of [ $path => $foldername] of each subfolder
   *    'files' => array of file details on each file in folder
   * ]
   */
  public function folderInfo($folder,$folder_prefix,$current_folder)
  {
    $folder = $this->cleanFolder($folder);
    $path = $current_folder;

    $breadcrumbs = $this->breadcrumbs($folder);
    $slice = array_slice($breadcrumbs, -1);
    $folderName = current($slice);
    $breadcrumbs = array_slice($breadcrumbs, 0, -1);

    $Folders = [];
    foreach (array_unique($this->disk->directories($folder)) as $subfolder) {
      $res_folder = ltrim(substr($subfolder, strrpos($subfolder, '/')),'/');
      if(!in_array($res_folder,$this->thumb_folders )){
        array_push($Folders,[
            'path' => str_replace($folder_prefix,"",$subfolder).'/',
            'name' => $res_folder
        ]);
        //$subfolders["/$subfolder"] = $res_folder;
      }
      
    }

    $Files = [];
    $this->checkIfFolderExist($folder.'/thumbs');
    foreach ($this->thumb_sizes as $thumb_code =>  $thumb_size) {
         $this->checkIfFolderExist($folder.'/thumbs_'.$thumb_code);
    }

    foreach ($this->disk->files($folder) as $filepath) {
      $mimeType = $this->fileMimeType($filepath);
      if(in_array($mimeType,config('labspace-upload-api.valid_mime'))){
        $Files[] = $this->fileDetails($filepath);
      }
        
    }

    return compact(
      //'folder',
      'path',
      //'folderName',
      //'breadcrumbs',
      'Folders',
      'Files'
    );
  }

  /**
   * Sanitize the folder name
   */
  protected function cleanFolder($folder)
  {
    return '/' . trim(str_replace('..', '', $folder), '/');
  }

  /**
   * Return breadcrumbs to current folder
   */
  protected function breadcrumbs($folder)
  {
    $folder = trim($folder, '/');
    $crumbs = ['/' => '根目錄'];

    if (empty($folder)) {
      return $crumbs;
    }

    $folders = explode('/', $folder);
    $build = '';
    foreach ($folders as $folder) {
      $build .= '/'.$folder;
      $crumbs[$build] = $folder;
    }

    return $crumbs;
  }

  /**
   * Return an array of file details for a file
   */
  protected function fileDetails($path)
  {
    $path = '/' . ltrim($path, '/');
    $data =  [
      'name' => ltrim(substr($path, strrpos($path, '/')),'/'),
      'path' => $path,
      'preview_url' => $this->fileWebpath($path),
      //'mimeType' => $this->fileMimeType($path),
      'type' => $this->fileType($path),
      //'size' => $this->fileSize($path),
      //'modified' => $this->fileModified($path),
    ];

    return $data;
  }

  /**
   * Return the full web path to a file
   */
  public function fileWebpath($path)
  {
    $mimeType = $this->fileMimeType($path);
    if(in_array($mimeType,['jpg','jpeg','png','gif','svg'])){
      $path = rtrim(config('labspace-upload-api.upload_url').'/', '/') . '/' .
        ltrim(get_thumbnail($path), '/');

    } else {
      $path = rtrim(config('labspace-upload-api.upload_url').'/', '/') . '/' .
        ltrim($path, '/');
    }
    
    return url($path);
  }

  /**
   * Return the mime type
   */
  public function fileMimeType($path)
  {
      
      $path_info =   pathinfo($path);
      return $path_info['extension'];
      
  }

  /**
   * Return the mime type
   */
  public function fileType($path)
  {
      
      $path_info =   pathinfo($path);
      $ext =  $path_info['extension'];
      if(in_array($ext,['jpg','jpeg','png','gif','svg'])){
        return 'image';
      }  else if(in_array($ext,['mp4'])){
        return 'video';
      } else {
        return $ext;
      }
      
  }

  /**
   * Return the file size
   */
  public function fileSize($path)
  {
    return $this->disk->size($path);
  }

  /**
   * Return the last modified time
   */
  public function fileModified($path)
  {
    return Carbon::createFromTimestamp(
      $this->disk->lastModified($path)
    );
  }

  /**
   * Create a new directory
   */
  public function createDirectory($folder)
  {
    $folder = $this->cleanFolder($folder);

    if ($this->disk->exists($folder)) {
      return "Folder '$folder' aleady exists.";
    }

    return $this->disk->makeDirectory($folder);
  }

  /**
   * Delete a directory
   */
  public function deleteDirectory($folder)
  {
    $folder = $this->cleanFolder($folder);

    $filesFolders = array_merge(
      $this->disk->directories($folder),
      $this->disk->files($folder)
    );
    /*if (! empty($filesFolders)) {
      return "Directory must be empty to delete it.";
    }*/

    return $this->disk->deleteDirectory($folder);
  }

  /**
   * Delete a file
   */
  public function deleteFile($path,$filename)
  {
    $path = $this->cleanFolder($path);

    if (! $this->disk->exists($path)) {
      return "File does not exist.";
    }

    //刪除原檔
    $origin_path  = str_replace($filename,'',$path );
    $this->disk->delete($origin_path.'thumbs/'.$filename);
    foreach ($this->thumb_sizes as $thumb_code =>  $thumb_size) {
          $this->disk->delete($origin_path.'thumbs/'.$thumb_code.'/'.$filename);
    }

    return $this->disk->delete($path);
  }

  /**
   * check folder
   */
  public function checkIfFolderExist($folder)
  {
    $folder = $this->cleanFolder($folder);

    if (!$this->disk->exists($folder)) {
      $this->createDirectory($folder);
    }
  }

  /**
   * Save a file
   */
  public function saveFile($folder,$filename, $file,$type='file')
  {

      $path = $this->cleanFolder($folder);
      //$folder  = $path.'/'.$filename;
      
      //原檔案
      $this->disk->put($folder.$filename, file_get_contents($file));

      if($type == 'image' || $type == 'admin-file-manager'){
        $thumb_folder =  $folder.'/thumbs/';
        $this->checkIfFolderExist($thumb_folder);
        //圖片需要基本縮圖
        $image_normal = Image::make($file)->resize(config('labspace-upload-api.thumbnail_width'), null, function ($constraint) {
            $constraint->aspectRatio();
        });
       
        $image_normal = $image_normal->stream();
       
        $this->disk->put($thumb_folder.$filename, $image_normal->__toString());

        foreach ($this->thumb_sizes as $thumb_code =>  $thumb_size) {
            $thumb_folder =  $folder.'/thumbs_'.$thumb_code.'/';
            $this->checkIfFolderExist($thumb_folder);
            //圖片需要基本縮圖
            $image_normal = Image::make($file)->resize($thumb_size, null, function ($constraint) {
                $constraint->aspectRatio();
            });
           
            $image_normal = $image_normal->stream();
            $this->disk->put($thumb_folder.$filename, $image_normal->__toString());
        }


      }

  }

}