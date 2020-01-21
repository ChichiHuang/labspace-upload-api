<?php

namespace Labspace\UploadApi\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ImageFileRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | 圖片file上傳
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
            'width' => 'required|min:0',
            'file' => 'required|image|mimes:jpeg,png,jpg,svg|max:'.config('labspace-upload-api.max_size'),
            'type' => 'required|in:'.config('labspace-upload-api.folder_names'),
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
        $response = [
            'status' => false,
            'err_code' => 'EMPTY_REQUEST',
            'err_msg' => '參數不完整',
            'err_detail' => null
        ];
        $response['err_detail']  = $validator->errors()->toArray();
        throw new HttpResponseException(response()->json($response, 422));
    }

   
}