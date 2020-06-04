<?php

namespace Labspace\UploadApi\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Exception;
use DB;
use Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Labspace\UploadApi\Services\TokenService;
use File;
use Labspace\UploadApi\Models\TempFile;
use Illuminate\Support\Facades\Input;
use Labspace\UploadApi\Requests\FileUploadRequest;
use Labspace\UploadApi\Services\ErrorService;
use Labspace\UploadApi\Exceptions\FileTypeInvalidException;
use Labspace\UploadApi\Services\FileManager\AdminManager;

class UploadController extends Controller
{


    protected $manager;

      public function __construct(AdminManager $manager)
      {
         $this->manager = $manager;
      }

    //ckeditor檔案上傳
    public function ckeditorJson(Request $request)
    {

        try{
            //註冊驗證
            $validator = Validator::make($request->all(), [
                'upload' => 'required|image|mimes:jpeg,png,jpg,gif|max:'.env('MAX_FILE_UPLOAD_KB',2000),
            ]);

            if ($validator->fails()) {
                throw new Exception('請上傳jpeg,png,jpg,gif 格式 (檔案限制:'.(env('MAX_FILE_UPLOAD_KB',2000)/1000).'MB )');
            } 
            
            $user_id = TokenService::getUserId($request);
            $path = '/'.$request->type.'/'.$user_id.'/';
            $image_source = new FileSource($request);  
            $manager = new ImageUpload($image_source);
            $manager->setImageInfo($request->width,$path);
            $filepath =  $manager->saveImage();
            return response()->json([
                'status' => true,
                'data'=> [
                    'filepath' => $filepath
                ],
                'success_code' => 'SUCCESS'
            ]);


           
        }  catch (Exception $e){
            return response()->json([
                'uploaded' => 0,
                'error' => ['message' => $e->getMessage()]
            ]);
        }
        
        
    }


    /**
   * Upload new file
   */
  public function fileUpload(Request $request)
  {


    try{
       $user_id = TokenService::getUserId($request);
        $path = '/files/'.$user_id.'/';
        $file= Input::file('file');

        $valid_mime_arr = config('labspace-upload-api.file_valid_mime');
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


         
          $filename =date('Ymd').date('His'). '.' .$file->getClientOriginalExtension(); 

          $this->manager->saveFile($path, $filename,File::get($file));
        TempFile::create(['filepath' => $path.$filename]);
        return response()->json([
            'status' => true,
            'data'=> [
                'filepath' => $path.$filename
            ],
            'success_code' => 'SUCCESS'
        ]);

    }  catch (Exception $e){
        $response = config('error_response.server_error');
        $response['err_detail'] = $e->getMessage();
        return response()->json($response ,500);

    }
   
  }


}