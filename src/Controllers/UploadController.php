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

class UploadController extends Controller
{


    public function __construct(

    ) {

    }


    //使用者登入
    public function login(LoginRequest $request)
    {
        DB::beginTransaction();
        try{
            $user = $this->authService->login($request->username,$request->password,$request->role);
            
        } catch (Exception $e){
            DB::rollBack();
            return ErrorService::response($e);
        }
        DB::commit();

        return $this->loginResponse($user);

        
    }

     //社群登入
    public function socialLogin(SocialLoginRequest $request)
    {
        //檢查socialAccount有沒有存在
        $provider = $request->provider;
        $provider_id = $request->social_id;

        $social_account = $this->socialAccountService->findByProviderAndId($provider,$provider_id);
        
        //帳號不存在
        if(!$social_account){
            //檢查信箱
            if($request->email && $request->email != ''){
                //信箱存不存在
                $user = $this->authService->findBy('username',$request->email,$request->role);
                if($user){
                    //信箱存在  自動建立社群連結
                    $this->socialAccountService->add([
                        'provider_id' => $provider_id,
                        'provider' => $provider,
                        'user_id' => $user->id
                    ]);
                } else {
                    //告知要註冊
                    return response()->json([
                        'status'=> true,
                        'data' => null,
                        'success_code'=> 'PLEASE_REGISTER'
                    ],200);
                }
            }
        } else {
            //帳號存在，找到對應使用者
            $user = $social_account->user;
        }

        
        return $this->loginResponse($user);
        
        
    }

    //登入後處理
    public function loginResponse($user)
    {
        //檢查使用者登入權限
        $this->authService->userCheck($user);
        
        return response()->json([
            'status' => true,
            'data' => $this->authService->getUserInfo($user),
            'success_code'=> 'SUCCESS'
        ],200);
                
    }



    //取得使用者資訊
    public function getUser(Request $request)
    {

        try{
            $user = auth()->user();
            if($user->login_permission == 0){
                throw new PermissionBanException();
            }

        } catch (Exception $e){
           
            return ErrorService::response($e);
        }
        
        return response()->json([
            'status' => true,
            'data' => [
                'user_info'=> $this->authService->getUserInfo($user)
            ],
            'success_code'=> 'SUCCESS'
            
        ]);
            
    }


  
    //無效化token
    public function logout(Request $request)
    {
        //檢查參數
        if (!$request->has('token') && !$request->header('token')){
            return response()->json([
                'status' => false,
                'err_code' => 'TOKEN_REQUIRED',
                'err_msg' => '請輸入token',
                'err_detail' => null
            ]);
        }
        try {
            if(!$request->header('token')){
                $oldToken = $request->input('token');
            } else {
                $oldToken = $request->header('token');
            }
            JWTAuth::setToken($oldToken)->invalidate();
            
        } catch (Exception $e){

            
        }
        
        return response()->json([
            'status' => true,
            'data'=> null,
            'success_code'=> 'SUCCESS'
        ],200);
    }

    //更新token
    public function refreshToken(Request $request)
    {
        $payloadable = [
            'role' =>  auth('api')->payload()->get('machine_id'),
            'exchange_rate' =>  auth('api')->payload()->get('exchange_rate')
        ];
        return $this->respondWithToken(JWTAuth::claims($payloadable)->parseToken()->refresh());
    }

  

	 /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'status' => true,
            'data' => [
                'token' => $token,
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ]
            
        ]);
    }

}