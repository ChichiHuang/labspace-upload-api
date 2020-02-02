<?php
namespace Labspace\UploadApi\Services;

use App;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ErrorService {
    
    /**
     * ErrorService constructor.
     *
     */
    public function __construct(

    ) {

    }


    //
    public static function response($e,$data='')
    {
    	//model not found
    	if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) 
        {   
            return response()->json([
                'status' => false,
                'err_code' => 'DATA_NOT_FOUND',
                'err_msg' => '資料不存在',
                'err_detail' =>  null
            ]);
        }

        //500
        if($e->getCode()==0){

            return response()->json([
                'status' => false,
                'err_code' => 'SERVER_ERROR',
                'err_msg' =>  '系統發生異常錯誤',
                'err_detail' =>  $e->getMessage()
            ],500);

	    //自定義
        } else {
            $msg_arr = explode('@', $e->getMessage());
            if(count($msg_arr) > 1){
                $err_code = $msg_arr[1];
            } else {
                $err_code = $e->getCode();
            }
            return response()->json([
                'status' => false,
                'err_code' => $err_code ,
                'err_msg' =>  $msg_arr[0],
                'err_detail' => null
            ]);

        }

        
    }
 
  
}
