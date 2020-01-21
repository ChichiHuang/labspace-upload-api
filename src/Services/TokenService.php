<?php
namespace Labspace\UploadApi\Services;

use App;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use App\Http\Requests;
use Exception;

class TokenService {


    /**
     * 依照token取得會員id
     * @param Request $request
     * @return integer
     */
    static public function getUserId(Request $request)
    {
        try {
            $auth_token = null;
            if(!$request->header('token')){
                if($request->input('token') != ''){
                    $auth_token = $request->input('token');
                }
                
            } else {
                if($request->header('token') != ''){
                   $auth_token = $request->header('token');
                }
                
            }
            if(!$auth_token){
                return 0;
            }
            $user = JWTAuth::toUser($auth_token); 
            return $user->id;

        } catch (Exception $e) {
            return 0;
        }       
        return 0;

    }


    /**
     * 取得token
     * @param Request $request
     * @return string
     */
    static public function tokenToUser(Request $request)
    {
        if(!$request->header('token')){
            $auth_token = $request->input('token');
            
        } else {
            $auth_token = $request->header('token');
            
        }
        return $auth_token;

    }
    

}