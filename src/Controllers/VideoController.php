<?php

namespace Labspace\UploadApi\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Http\Requests;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use Labspace\UploadApi\Services\VideoUpload;
use Labspace\UploadApi\Services\Video\FileSource;
use Labspace\UploadApi\Requests\VideoFileRequest;
use Labspace\UploadApi\Requests\VideoCkeditorRequest;
use Labspace\UploadApi\Services\TokenService;

class VideoController extends Controller
{

    public function __construct() {

    }


    //一般檔案上傳
    public function fileSource(VideoFileRequest $request)
    {
        try{
            $user_id = TokenService::getUserId($request);
            $path = '/'.$request->type.'/'.$user_id.'/';
            $source = new FileSource($request);  
            $manager = new VideoUpload($source);
            $manager->setFileInfo($path);
            $filepath =  $manager->saveFile();
            return response()->json([
                'status' => true,
                'data'=> [
                    'filepath' => $filepath
                ],
                'success_code' => 'SUCCESS'
            ]);

        }  catch (Exception $e){
            $response = config('error_response.server_error');
            $response['err_detail'] = $e->getMessage();
            return response()->json($response ,500);

        }
        
        
    }

    //ckeditor檔案上傳
    public function ckeditorJson(Request $request)
    {
        try{

            //註冊驗證
            $validator = Validator::make($request->all(), [
                'upload' => 'required|mimes:mp4,mp3|max:'.env('MAX_FILE_UPLOAD_KB',2000),
            ]);

            if ($validator->fails()) {
                throw new Exception('請上傳mp4,mp3 格式 (檔案限制:'.(env('MAX_FILE_UPLOAD_KB',2000)/1000).'MB )');
            } 


            if(!$request->has('id')){
                $id = 0;
            } else {
                $id = $request->id;
            }
            $path = '/diary/'.$id.'/';
            $source = new FileSource($request,null,'upload');  
            $manager = new VideoUpload($source);
            $manager->setFileInfo($path);
            $filepath =  $manager->saveFile();

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