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
            $user = auth()->user();
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