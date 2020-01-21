<?php

namespace Labspace\UploadApi\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ImageCkeditorRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | 圖片ckeditor file上傳
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
            'upload' => 'required|image|mimes:jpeg,png,jpg,gif|max:'.config('labspace-upload-api.max_size'),
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
            'error' => ['message' => '圖片只開放jpg、png、gif，檔案大小限制：'.(config('labspace-upload-api.max_size')/1000).'MB']
        ], 200));
    }

   
}