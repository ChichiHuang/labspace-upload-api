<?php

namespace Labspace\UploadApi\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class VideoCkeditorRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | 影片file上傳
    |--------------------------------------------------------------------------
    */
    public function authorize()
    {
        return true;
    }

    /**
     * 驗證規則
     */
    public function rules()
    {
        return [
            'upload' => 'required|mimes:mp4,mp3|max:'.config('labspace-upload-api.max_size'),
        ];
    }

    /**
     * 回傳訊息
     */
    public function messages()
    {
        return [

        ];
    }

    protected function failedValidation(Validator $validator) {
        throw new HttpResponseException(response()->json([
            'uploaded' => 0,
            'error' => ['message' => '影片：開放mp4格式，音訊：開放mp3格式，檔案大小限制：'.(config('labspace-upload-api.max_size')/1000).'MB']
        ], 200));
    }

   
}