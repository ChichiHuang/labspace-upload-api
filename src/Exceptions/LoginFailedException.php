<?php
namespace Labspace\AuthApi\Exceptions;

use Exception;

class LoginFailedException extends Exception
{
    protected $message = '帳號或密碼錯誤@WRONG_USER';
    protected $code = '403';


}
