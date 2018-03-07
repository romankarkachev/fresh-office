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
}
