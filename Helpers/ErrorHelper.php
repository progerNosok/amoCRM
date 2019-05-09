<?php

namespace Helpers;

use Exception;

class ErrorHelper
{
    private static $errors = array(
        301=>'Moved permanently',
        400=>'Bad request',
        401=>'Unauthorized',
        403=>'Forbidden',
        404=>'Not found',
        500=>'Internal server error',
        502=>'Bad gateway',
        503=>'Service unavailable'
    );

    //Обрабатывает ответ, полученный от сервера
    public static function CheckCurlResponse($code)
    {
        $code=(int)$code;

        try
        {
            #Если код ответа не равен 200 или 204 - возвращаем сообщение об ошибке
            if($code!=200 && $code!=204)
                throw new Exception(isset($errors[$code]) ? self::$errors[$code] : 'Undescribed error',$code);
        }
        catch(Exception $E)
        {
            die('Ошибка: '.$E->getMessage().PHP_EOL.'Код ошибки: '.$E->getCode());
        }
    }

    public static function showErrors()
    {
        ini_set('display_errors', 'On'); // сообщения с ошибками будут показываться
        error_reporting(E_ALL); // E_ALL - отображаем ВСЕ ошибки
    }
}