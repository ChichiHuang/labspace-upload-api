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
use Labspace\AuthApi\Services\ErrorService;
use File;

class UploadController extends Controller
{


    public function __construct(

    ) {

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


}