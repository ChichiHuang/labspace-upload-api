<?php

namespace Labspace\UploadApi\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Http\Requests;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use Labspace\UploadApi\Services\ImageUpload;
use Labspace\UploadApi\Services\Image\Base64Source;
use Labspace\UploadApi\Services\Image\FileSource;
use Labspace\UploadApi\Requests\ImageBase64Request;
use Labspace\UploadApi\Requests\ImageFileRequest;
use Labspace\UploadApi\Requests\ImageCkeditorRequest;
use Input;
use Labspace\UploadApi\Services\TokenService;
use Labspace\UploadApi\Models\TempFile;

class ImageController extends Controller
{

    public function __construct() {

    }

    //base64形式上傳檔案
    public function base64Source(ImageBase64Request $request)
    {
        try{
            $user_id = TokenService::getUserId($request);
            $path = '/'.$request->type.'/'.$user_id.'/';
            $image_source = new Base64Source($request);  
            $manager = new ImageUpload($image_source);
            $manager->setImageInfo(700,$path);
            $filepath =  $manager->saveImage();
            TempFile::create(['filepath' => $filepath]);
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

    //一般檔案上傳
    public function fileSource(ImageFileRequest $request)
    {
       
        try{
           $user_id = TokenService::getUserId($request);
            $path = '/'.$request->type.'/'.$user_id.'/';
            $image_source = new FileSource($request);  
            $manager = new ImageUpload($image_source);
            $manager->setImageInfo($request->width,$path);
            $filepath =  $manager->saveImage();
            TempFile::create(['filepath' => $filepath]);
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
    public function ckeditor(Request $request)
    {
        try{
            $user_id = TokenService::getUserId($request);
            $path = '/ckeditor/'.$user_id.'/';
            $image_source = new FileSource($request,null,'upload');  
            $manager = new ImageUpload($image_source);
            $manager->setImageInfo(800,$path);
            $filepath =  $manager->saveImage();
            $message = $url = '';
            $funcNum = $request->CKEditorFuncNum;
            $url = env('UPLOAD_URL').$filepath;
            echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '".$url."', '');</script>";
           
        }  catch (Exception $e){
            echo "<script   type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '', '" . $e->getMessage() . "');</script>";
        }
        
        
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
            $path = '/ckeditor/'.$user_id.'/';
            $image_source = new FileSource($request,null,'upload');  
            $manager = new ImageUpload($image_source);
            $manager->setImageInfo(800,$path);
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