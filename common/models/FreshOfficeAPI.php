<?php

namespace common\models;

use yii\base\Model;

/**
 * Класс для работы с API CRM-системы Fresh Office.
 */
class FreshOfficeAPI extends Model
{
    /**
     * Признаки оплаты.
     * Таблица в SQL: SUB_PRIZNAK_MANY.
     */
    const FINANCES_PAYMENT_SIGN_УТИЛИЗАЦИЯ = 1;
    const FINANCES_PAYMENT_SIGN_ТРАНСПОРТ = 2;

    /**
     * Направления движения.
     * Таблица в SQL: LIST_SPR_NAPR_MONY.
     */
    const FINANCES_DIRECTION_ПРИХОД = 1;
    const FINANCES_DIRECTION_РАСХОД = 2;

    /**
     * Типы сообщений.
     * Таблица в SQL: LIST_TIP_NOTEPAD_MESSAGE.
     */
    const MESSAGES_TYPE_СООБЩЕНИЕ = 1;
    const MESSAGES_TYPE_ПРЕДЛОЖЕНИЕ = 2;
    const MESSAGES_TYPE_ОБЪЯВЛЕНИЕ = 4;
    const MESSAGES_TYPE_РАСПОРЯЖЕНИЕ = 5;

    /**
     * Статусы сообщений.
     * Таблица в SQL: LIST_PRIZNAK_NOTEPAD_MESSAGE.
     */
    const MESSAGES_STATUS_ПРОЧИТАНО = 1;
    const MESSAGES_STATUS_НЕПРОЧИТАНО = 2;

    /**
     * Категории задач.
     */
    const TASK_CATEGORY_СТАНДАРТНАЯ = 3;
    const TASK_CATEGORY_ВЫСОКАЯ = 4;

    /**
     * Статусы задач.
     * Таблица в SQL: PRIZNAK_CONTACT.
     */
    const TASKS_STATUS_ЗАПЛАНИРОВАН = 1;
    const TASKS_STATUS_ВЫПОЛНЕН = 2;
    const TASKS_STATUS_В_ПРОЦЕССЕ = 3;

    /**
     * Типы задач.
     * Таблица в SQL: VID_CONTACT.
     */
    const TASK_TYPE_ВХОДЯЩИЙ = 1; // новый клиент
    const TASK_TYPE_ВСТРЕЧА = 2;
    const TASK_TYPE_НАПОМИНАНИЕ = 3;
    const TASK_TYPE_СОГЛАСОВАНИЕ_ВЫВОЗА = 7;
    const TASK_TYPE_КОНТРОЛЬ_КАЧЕСТВА = 8;
    const TASK_TYPE_НЕСООТВЕТСТВИЕ_ГРУЗА_ТТН  = 9;
    const TASK_TYPE_ОБРАЩЕНИЕ = 17;
    const TASK_TYPE_СВЯЗЬ_ЧЕРЕЗ_ЛК = 20;

    /**
     * Типы контрагентов.
     */
    const COMPANY_TYPE_ЮРЛИЦО = 1;
    const COMPANY_TYPE_ФИЗЛИЦО = 1;

    /**
     * Статусы клиентов.
     */
    const COMPANY_STATE_НОВАЯ_КОМПАНИЯ = 1;

    /**
     * Группа контрагента.
     * Таблица в SQL: GROUPS_COMPANY.
     */
    const COMPANY_GROUP_ОТДЕЛ_ВХОДЯЩИХ_ЗАЯВОК = 10;

    /**
     * Статус контактного лица.
     */
    const CONTACT_PERSON_STATE_РАБОТАЕТ = 1;

    /**
     * Типы проектов.
     * Таблица: LIST_SPR_PROJECT.
     */
    const PROJECT_TYPE_САМОПРИВОЗ = 6;
    const PROJECT_TYPE_ДОКУМЕНТЫ = 12;

    /**
     * Статусы проектов.
     * Таблица: LIST_SPR_PRIZNAK_PROJECT.
     */
    const PROJECT_STATE_ТРАНСПОРТ_ЗАКАЗАН = 30;

    const API_ID = 1492;
    const API_PASSWORD = 'LKvBA1dgIP48Osbco6q3E1Dw0F5-gk3d';
    const API_URL = 'https://api.myfreshcloud.com/';

    /**
     * Выполняет GET-запрос с базовой аутентификацией для получения данных по API FreshOffice.
     * @param string $entity
     * @param string $select
     * @param string $filter
     * @param string $expand
     * @return mixed
     */
    public static function makeGetRequestToApi($entity, $select = null, $filter = null, $expand = null)
    {
        $api_url = self::API_URL . $entity . '/';
        $auth_key = base64_encode(self::API_ID . ':' . self::API_PASSWORD);

        $data = [];
        if ($filter != null) $data['$filter'] = $filter;
        if ($select != null) $data['$select'] = $select;
        if (sizeof($data)) {
            $api_url .= '?'.http_build_query($data);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic '.$auth_key,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * Выполняет POST-запрос с базовой аутентификацией для отправки данных по API FreshOffice.
     * @param string $entity
     * @return mixed
     */
    public static function makePostRequestToApi($entity, $post_data)
    {
        $api_url = self::API_URL . $entity . '/';
        $auth_key = base64_encode(self::API_ID . ':' . self::API_PASSWORD);
        $post_data = json_encode($post_data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json;odata=verbose',
            'Authorization: Basic '.$auth_key,
        ]);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * Выполняет создание нового проекта по заданным параметрам.
     * @param $ca_id integer идентификатор контрагента
     * @param $manager_id integer идентификатор менеджера контрагента (ответственный по нему)
     * @param $project_type_id integer идентификатор типа создаваемого проекта
     * @return string
     */
    public static function foapi_createNewProject($ca_id, $manager_id, $project_type_id)
    {
        $params = [
            'id_company' => $ca_id,
            //'id_contact_man' => 2242,
            'id_manager' => $manager_id,
            'id_list_project' => $project_type_id,
            'id_priznak_project' => ProjectsStates::STATE_СОГЛАСОВАНИЕ_ВЫВОЗА,
            //'date_create_project' => '2012-11-01T18:28:08',
            //'date_chanch' => '2012-11-01T18:28:08',
            //'date_final_project' => '2012-11-01T18:28:08',
            'id_manager_ver' => $manager_id,
            'id_manager_creator' => $manager_id,
            'prim_project_company' => 'Проект создан заказчиком из его личного кабинета',
            /*
            'id_ch' => null,
            'date_start_project' => '2012-11-01T18:28:08',
            'use_n_pp' => 'True',
            'manager_trash' => null,
            'date_trash' => null,
            'marker_on' => null,
            'id_manager_marker' => null,
            'marker_description' => null,
            */
        ];

        $response = FreshOfficeAPI::makePostRequestToApi('project', $params);
        // проанализируем результат, который возвращает API Fresh Office
        $decoded_response = json_decode($response, true);
        if (isset($decoded_response['error'])) {
            $inner_message = '';
            if (isset($decoded_response['error']['innererror']))
                $inner_message = ' ' . $decoded_response['error']['innererror']['message'];
            // возникла ошибка при выполнении
            return 'При создании проекта возникла ошибка: ' . $decoded_response['error']['message']['value'] . $inner_message;
        }
        elseif (isset($decoded_response['d']))
            // фиксируем идентификатор задачи, которая была успешно создана
            return $decoded_response['d']['id_list_project_company'];
    }
}
