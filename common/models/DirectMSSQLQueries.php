<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * Класс для работы с Microsoft SQL Server напрямую.
 */
class DirectMSSQLQueries extends Model
{
    /**
     * Выполняет создание записи в базе данных SQL в таблице Сообщения.
     * @param $sender_id integer идентификатор отправителя
     * @param $receiver_id integer идентификатор получателя
     * @param $message string текст сообщения
     */
    public static function createNewMessageForManager($sender_id, $receiver_id, $message)
    {
        Yii::$app->db_mssql->createCommand()->insert('CBaseCRM_Fresh_7x.dbo.LIST_NOTEPAD_TXT', [
            'ID_MANAGER' => $receiver_id,
            'ID_MANAGER_SEND' => $sender_id,
            'TEXT_MESSAGE' => $message,
            'DATA_TXT' => new Expression('GETDATE()'),
            'TIME_TXT' => new Expression('GETDATE()'),
            'ID_TIP_LOCAL_MESSAGE' => FreshOfficeAPI::MESSAGES_TYPE_СООБЩЕНИЕ,
            'ID_PRIZNAK_NOTEPAD_MESSAGE' => FreshOfficeAPI::MESSAGES_STATUS_НЕПРОЧИТАНО,
            'ID_LIST_PROJECT_COMPANY' => 0,
        ])->execute();
    }

    /**
     * Выполняет изменение ответственного лица по контрагенту.
     * Примеры запросов, которые выполняются:
     * UPDATE [CBaseCRM_Fresh_7x].[dbo].[COMPANY] SET [ID_MANAGER]=70 WHERE [ID_COMPANY] IN ('3018', '1')
     * UPDATE [CBaseCRM_Fresh_7x].[dbo].[COMPANY] SET [ID_MANAGER]=70 WHERE [ID_COMPANY]='3018'
     */
    public static function changeResponsible($ca_id, $manager_id)
    {
        $rows_affected = Yii::$app->db_mssql->createCommand()->update('CBaseCRM_Fresh_7x.dbo.COMPANY', [
            'ID_MANAGER' => $manager_id,
        ], [
            'ID_COMPANY' => $ca_id,
        ])->execute();
        //])->getRawSql();

        if ($rows_affected >= 0)
            return true;
        else
            return false;
    }

    /**
     * Возвращает в виде массива менеджеров CRM.
     * @return array
     */
    public static function fetchManagers()
    {
        $query_text = '
SELECT
    ID_MANAGER AS id,
    MANAGER_NAME AS name
FROM CBaseCRM_Fresh_7x.dbo.MANAGERS
ORDER BY name';

        return Yii::$app->db_mssql->createCommand($query_text)->queryAll();
    }

    /**
     * Делает выборку менеджеров и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfManagersForSelect2()
    {
        return ArrayHelper::map(self::fetchManagers() , 'id', 'name');
    }
}
