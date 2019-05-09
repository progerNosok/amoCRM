<?php

namespace App;

use Helpers\ErrorHelper;

class Task
{
    private $leadIdListWithoutTask = [];

    public function __construct()
    {
        $this->createLeadList();
    }

    private function createLeadList()
    {
        //Формирую ссылку для запроса с фильтром выборки сделок без задач
        $link = 'https://' . SUBDOMAIN . '.amocrm.ru/private/api/v2/json/leads/list';

        $curl = curl_init(); #Сохраняем дескриптор сеанса cURL
        #Устанавливаем необходимые опции для сеанса cURL
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
        curl_setopt($curl, CURLOPT_URL, $link);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/../cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
        curl_setopt($curl, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/../cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        $out = curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE); #Получим HTTP-код ответа сервера
        curl_close($curl); #Завершаем сеанс cURL

        ErrorHelper::CheckCurlResponse($code);

        /**
         * Данные получаем в формате JSON, поэтому, для получения читаемых данных,
         * нам придётся перевести ответ в формат, понятный PHP
         */
        $Response = json_decode($out, true);
        $leads_list = $Response['response'];

        if (empty($leads_list['leads']))
        {
            die("Сделки отсутствуют");
        }

        $this->fillLeadIdListWithoutTask($leads_list);

    }

    private function fillLeadIdListWithoutTask(array $leadList)
    {
        foreach ($leadList['leads'] as $leads)
        {
            if (is_array($leads) && isset($leads['id']))
            {
                if ($leads['closest_task'] == 0)
                {
                    $this->leadIdListWithoutTask[] = $leads['id'];
                }
            }
            else
            {
                die('Невозможно получить поле "ID сделки"');
            }
        }
    }

    public function createTask()
    {
        $tasks['request']['tasks']['add'] = [];

        foreach($this->leadIdListWithoutTask as $id)
        {
            $tasks['request']['tasks']['add'][] = [
                'element_id' => $id,
                'element_type' => 2, #Показываем, что это - сделка, а не контакт
                'task_type' => 1, #Звонок
                'text' => 'Сделка без задачи',
                'responsible_user_id' => 109999,
                'complete_till_at' => strtotime('now')
            ];
        }


        $link='https://'.SUBDOMAIN.'.amocrm.ru/private/api/v2/json/tasks/set';

        $curl=curl_init(); #Сохраняем дескриптор сеанса cURL
        #Устанавливаем необходимые опции для сеанса cURL
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_USERAGENT,'amoCRM-API-client/1.0');
        curl_setopt($curl,CURLOPT_URL,$link);
        curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($tasks));
        curl_setopt($curl,CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
        curl_setopt($curl,CURLOPT_HEADER,false);
        curl_setopt($curl,CURLOPT_COOKIEFILE,dirname(__FILE__).'/../cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
        curl_setopt($curl,CURLOPT_COOKIEJAR,dirname(__FILE__).'/../cookie.txt'); #PHP>5.3.6 dirname(__FILE__) -> __DIR__
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);

        $out=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
        $code=curl_getinfo($curl,CURLINFO_HTTP_CODE);

        ErrorHelper::CheckCurlResponse($code);

        echo "Для всех сделок без задач создана новая задача";
    }
}