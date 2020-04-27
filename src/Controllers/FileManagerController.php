<?php
namespace Labspace\UploadApi\Controllers;

use App\Http\Controllers\Controller;
use Labspace\UploadApi\Services\FileManager\AdminManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Image;
use Log;
use Exception;
use Validator;
use Labspace\UploadApi\Services\ImageUpload;
use Labspace\UploadApi\Services\Image\Base64Source;
use Labspace\UploadApi\Services\Image\FileSource;
use Labspace\UploadApi\Requests\FileUploadRequest;
use Labspace\UploadApi\Requests\FileManagerRequest;
use Labspace\UploadApi\Services\ErrorService;
use Labspace\UploadApi\Exceptions\FileTypeInvalidException;
use Auth;

class FileManagerController extends Controller
{
  protected $manager;

  public function __construct(AdminManager $manager)
  {
     $this->manager = $manager;
  }

  /**
   * Show page of files / subfolders
   */
  public function index(FileManagerRequest $request)
  {
    $user = auth()->user();
    if($user->role == 'admin'){
      $path = 'admin';
    } else {
      $path = $user->id;
    }
    $folder = 'manager/'.$path.$request->get('path');
    if(!$request->has('type')){
      $type = 'N';
    } else {
      $type = $request->type;
    }

    $arr = $this->manager->folderInfo($folder,$folder_prefix='manager/'.$path ,$request->path);

    $data = $arr;



    //dd($data);
    return response()->json([
        'status' => true,
        'data'=> $data,
        'success_code' => 'SUCCESS'
    ]);
  }

  /**
   * Create a new folder
   */
  public function createFolder(Request $request)
  {
      $user = auth()->user();
      if($user->role == 'admin'){
        $path = 'admin';
      } else {
        $path = $user->id;
      }
      try{
          $new_folder = str_replace( " ", "",trim($request->get('name')));
          $folder = 'manager/'.$path.$request->get('path').$new_folder;
          //return $folder;
          $result = $this->manager->createDirectory($folder);

        return response()->json([
            'status' => true,
            'data'=> null,
            'success_code' => 'SUCCESS'
        ]);

      }  catch (Exception $e){
          return ErrorService::response($e);
      }
  }

  /**
   * Delete a file
   */
  public function deleteFile(FileUploadRequest $request)
  {

    try{
        $user = auth()->user();
        if($user->role == 'admin'){
          $path = 'admin';
        } else {
          $path = $user->id;
        }
        $del_file = $request->get('file');
        $filepath = 'manager/'.$path.$request->get('path').$del_file;

        $this->manager->deleteFile($filepath,$del_file);

        return response()->json([
            'status' => true,
            'data'=> null,
            'success_code' => 'SUCCESS'
        ]);

    }  catch (Exception $e){
        return ErrorService::response($e);
    }
  }

  /**
   * Delete a folder
   */
  public function deleteFolder(Request $request)
  {

      try{
        $user = auth()->user();
        if($user->role == 'admin'){
          $path = 'admin';
        } else {
          $path = $user->id;
        }
        $del_folder = $request->get('name');
        $folder = 'manager/'.$path.$request->get('path').'/'.$del_folder;

        $result = $this->manager->deleteDirectory($folder);

        return response()->json([
            'status' => true,
            'data'=> null,
            'success_code' => 'SUCCESS'
        ]);

      }  catch (Exception $e){
          return ErrorService::response($e);
      }
  }

  /**
   * Upload new file
   */
  public function fileUpload(FileUploadRequest $request)
  {

    try{
        $user = auth()->user();
        if($user->role == 'admin'){
          $path = 'admin';
        } else {
          $path = $user->id;
        }
        

        $file= Input::file('file');

        $valid_mime_arr = config('labspace-upload-api.valid_mime');
        $mimes = '';
        foreach ($valid_mime_arr as  $valid_mime) {
           $mimes= $mimes.$valid_mime.',';
        }
        

         //註冊驗證
          $validator = Validator::make(['file' => $file], [
              'file' => 'mimes:'.$mimes.'|max:'.config('labspace-upload-api.max_size'),
          ]);

          if ($validator->fails()) {
              throw new FileTypeInvalidException();
          } 



          $folder = 'manager/'.$path.$request->get('path').'/';

          $filename = $file->getClientOriginalName(); 
          $filename =str_replace( " ", "",trim($filename));
          $filename =str_replace("(","[",$filename);
          $filename =str_replace(")","]", $filename);
          $filename =clean_file_name($filename);

          $this->manager->saveFile($folder, $filename,File::get($file));
          //檢查縮圖資料夾
          $this->manager->checkIfFolderExist($folder.'thumbs/');
          //縮圖
          $file_path = 'app/public/'.$folder.'thumbs/'.$filename ;

          //檢查非mp4才要縮圖
          $validator = Validator::make(['file' => $file], [
              'file' => 'mimes:jpg,jpeg,png,gif,svg',
          ]);

          if (!$validator->fails()) {
              //縮圖檔案處理
              $img = Image::make($file->getRealPath());
              $img->resize(200, null, function ($constraint) {
                  $constraint->aspectRatio();
              });
              $img->save(storage_path($file_path));
          } 

           
    }  catch (Exception $e){

        return ErrorService::response($e);
    }

    return response()->json([
        'status' => true,
        'data'=> null,
        'success_code' => 'SUCCESS'
    ]);
   
  }

  //ckeditor檔案上傳
    public function ckeditorUpload(Request $request)
    {
        try{
            //註冊驗證
            $validator = Validator::make($request->all(), [
                'upload' => 'required|image|mimes:jpeg,png,jpg,gif',
            ]);

            if ($validator->fails()) {
                throw new Exception('請上傳jpeg,png,jpg,gif 格式 ');
            } 


            if(!$request->has('writer')){
               $path = '/editor/';
            } else {
                $id = Auth::user()->id;
                $path = '/writer_editor/'.$id.'/';
            }
            $file= Input::file('upload');
            $filename = date('Ymd').'-'.date('His').'.'.$file->getClientOriginalExtension();
            $image_source = new FileSource($request,$filename,'upload');  
            $manager = new ImageUpload($image_source);
            $manager->setImageInfo(1920,$path);
            $filepath =  $manager->saveImage();

            $url = env('UPLOAD_URL').$filepath;
            return response()->json([
                'uploaded' => 1,
                'fileName' => $url,
                'url' => $url
            ]);
           
        }  catch (Exception $e){
            return response()->json([
                'uploaded' => 0,
                'error' => ['message' => $e->getMessage()]
            ]);
        }
        
        
    }
}