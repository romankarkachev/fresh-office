<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;

/**
 * Класс для работы с Microsoft SQL Server напрямую.
 */
class DirectMSSQLQueries extends Model
{
    /**
     * Типы проектов.
     * заказ предоплата, вывоз, заказ постоплата, самопривоз, фото/видео, выездные работы, производство, осмотр объекта
     */
    const PROJECTS_TYPES_LOGIST_LIMIT = '3,4,5,6,7,8,10,14';

    /**
     * Статусы заказов.
     * оплачено, у заказчика, на складе, едет на склад, едет к заказчику, вывоз завершен, вывоз согласован,
     * самопривоз одобрен, согласование вывоза, транспорт заказан
     */
    const PROJECTS_STATES_LOGIST_LIMIT = '5,6,13,28,29,30,31,32,33,34';

    /**
     * Делает выборку типов проектов.
     * @param $logistLimit bool применение идентификаторов, ограничивающих выборку (для логистов)
     * @return array
     */
    public static function fetchProjectsTypes($logistLimit = null)
    {
        $conditionIds = '';
        if (isset($logistLimit)) $conditionIds = chr(13) . 'WHERE ID_LIST_SPR_PROJECT IN (' . self::PROJECTS_TYPES_LOGIST_LIMIT . ')';

        $query_text = '
SELECT ID_LIST_SPR_PROJECT AS id ,NAME_PROJECT AS name
FROM CBaseCRM_Fresh_7x.dbo.LIST_SPR_PROJECT' . $conditionIds . '
ORDER BY NAME_PROJECT';

        return Yii::$app->db_mssql->createCommand($query_text)->queryAll();
    }

    /**
     * Делает выборку статусов проектов.
     * @param $logistLimit bool применение идентификаторов, ограничивающих выборку (для логистов)
     * @return array
     */
    public static function fetchProjectsStates($logistLimit = null)
    {
        $conditionIds = '';
        if (isset($logistLimit)) $conditionIds = chr(13) . 'WHERE ID_PRIZNAK_PROJECT IN (' . self::PROJECTS_STATES_LOGIST_LIMIT . ')';

        $query_text = '
SELECT ID_PRIZNAK_PROJECT AS id, PRIZNAK_PROJECT AS name
FROM CBaseCRM_Fresh_7x.dbo.LIST_SPR_PRIZNAK_PROJECT' . $conditionIds . '
ORDER BY PRIZNAK_PROJECT';

        return Yii::$app->db_mssql->createCommand($query_text)->queryAll();
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
     * Возвращает данные контрагента.
     * @param $id integer идентификатор контрагента
     * @return array
     */
    public static function fetchCounteragent($id)
    {
        $query_text = '
SELECT
    COMPANY.ID_COMPANY AS caId, COMPANY_NAME AS caName,
    MANAGERS.ID_MANAGER AS managerId, MANAGERS.MANAGER_NAME AS managerName
FROM CBaseCRM_Fresh_7x.dbo.COMPANY
LEFT JOIN MANAGERS ON MANAGERS.ID_MANAGER = COMPANY.ID_MANAGER
WHERE COMPANY.ID_COMPANY=' . intval($id);

        return Yii::$app->db_mssql->createCommand($query_text)->queryAll();
    }

    /**
     * Возвращает данные контрагента.
     * @param $name string подстрока для поиска по наименованию
     * @return array
     */
    public static function fetchCounteragents($name)
    {
        $query_text = '
SELECT
    COMPANY.ID_COMPANY AS id, COMPANY_NAME AS text
FROM CBaseCRM_Fresh_7x.dbo.COMPANY
WHERE COMPANY.COMPANY_NAME LIKE \'%' . $name . '%\'
ORDER BY COMPANY_NAME';

        return Yii::$app->db_mssql->createCommand($query_text)->queryAll();
    }

    /**
     * Делает выборку проектов.
     */
    public static function fetchProjects()
    {
        $query_text = '
SELECT LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY AS id
,LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT AS type_id, LIST_SPR_PROJECT.NAME_PROJECT AS type_name
,ADD_vivozdate
,DATE_START_PROJECT AS date_start
,DATE_FINAL_PROJECT AS date_end
,LIST_PROJECT_COMPANY.ID_COMPANY AS ca_id, COMPANY.COMPANY_NAME AS ca_name
,LIST_PROJECT_COMPANY.ID_MANAGER_VED AS manager_id, MANAGERS.MANAGER_NAME AS manager_name
,payment.amount
,payment.cost
,LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT AS state_id, LIST_SPR_PRIZNAK_PROJECT.PRIZNAK_PROJECT AS state_name
,ADD_perevoz
,ADD_proizodstvo
,ADD_oplata
,ADD_adres
,ADD_dannie
,ADD_ttn
,ADD_wieght AS weight
FROM [CBaseCRM_Fresh_7x].[dbo].[LIST_PROJECT_COMPANY]
LEFT JOIN LIST_SPR_PROJECT ON LIST_SPR_PROJECT.ID_LIST_SPR_PROJECT = LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT
LEFT JOIN LIST_SPR_PRIZNAK_PROJECT ON LIST_SPR_PRIZNAK_PROJECT.ID_PRIZNAK_PROJECT = LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT
LEFT JOIN COMPANY ON COMPANY.ID_COMPANY = LIST_PROJECT_COMPANY.ID_COMPANY
LEFT JOIN MANAGERS ON MANAGERS.ID_MANAGER = LIST_PROJECT_COMPANY.ID_MANAGER_VED
LEFT JOIN (
	SELECT ID_LIST_PROJECT_COMPANY, SUM(PRICE_TOVAR) AS amount, SUM(SS_PRICE_TOVAR) AS cost
	FROM [CBaseCRM_Fresh_7x].[dbo].[LIST_TOVAR_PROJECT]
	GROUP BY ID_LIST_PROJECT_COMPANY
) AS payment ON payment.ID_LIST_PROJECT_COMPANY = LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY
WHERE
  LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT IN (' . self::PROJECTS_STATES_LOGIST_LIMIT . ')
  AND LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT IN (' . self::PROJECTS_TYPES_LOGIST_LIMIT . ')
ORDER BY LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY DESC';
//WHERE LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY=352';

        $result = Yii::$app->db_mssql->createCommand($query_text)->queryAll();
        return new ArrayDataProvider([
            'modelClass' => 'common\models\ProjectsFO',
            'key' => 'id',
            'allModels' => $result,
            'pagination' => [
                //'pageSize' => $this->searchPerPage,
            ],
            'sort' => [
                'defaultOrder' => ['date_start' => SORT_ASC],
                'attributes' => [
                    'id',
                    'type_id',
                    'type_name',
                    'state_id',
                    'state_name',
                    'manager_id',
                    'manager_name',
                    'ca_id',
                    'ca_name',
                    'amount',
                    'cost',
                    'vivozdate',
                    'date_start',
                    'date_end',
                    'perevoz',
                    'proizodstvo',
                    'oplata',
                    'adres',
                    'dannie',
                    'ttn',
                    'weight',
                ],
            ],
        ]);
    }

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
     * Делает выборку проектов с суммой, себестоимостью, перевозчиком, датой оплаты рейса.
     * Выбрка только по идентификаторам, переданным в параметрах.
     * @param $project_ids string идентификаторы проектов через запятую
     * @return array
     */
    public static function fetchProjectsForDatePayment($project_ids)
    {
        if ($project_ids == null || $project_ids == '') return [];

        $query_text = '
SELECT
    LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY AS id,
    ADD_perevoz AS ferryman_name,
    ADD_oplata AS date_payment,
    payment.amount,
    payment.cost
FROM [CBaseCRM_Fresh_7x].[dbo].[LIST_PROJECT_COMPANY]
LEFT JOIN (
	SELECT ID_LIST_PROJECT_COMPANY, SUM(PRICE_TOVAR) AS amount, SUM(SS_PRICE_TOVAR) AS cost
	FROM [CBaseCRM_Fresh_7x].[dbo].[LIST_TOVAR_PROJECT]
	GROUP BY ID_LIST_PROJECT_COMPANY
) AS payment ON payment.ID_LIST_PROJECT_COMPANY = LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY
WHERE LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY IN (' . $project_ids . ')';

        return Yii::$app->db_mssql->createCommand($query_text)->queryAll();
    }

    /**
     * Выполняет изменение даты оплаты проекта.
     * @return bool
     */
    public static function updateProjectsAddOplata($project_id, $date_payment)
    {
        $rows_affected = Yii::$app->db_mssql->createCommand()->update('CBaseCRM_Fresh_7x.dbo.LIST_PROJECT_COMPANY', [
            'ADD_oplata' => new Expression('CONVERT(datetime, \''. $date_payment .'T00:00:00.000\', 126)'),
        ], [
            'ID_LIST_PROJECT_COMPANY' => intval($project_id),
        ])->execute();

        if ($rows_affected >= 0)
            return true;
        else
            return false;
    }

    /**
     * Выполняет дополнение массива обращений, переданного в параметрах, наименованиями контрагентов.
     * @param $appeals array массив обращений
     * @param $ca_ids string идентификаторы контрагентов через запятую
     * @return array
     */
    public static function fillAppealsArrayWithNames($appeals, $ca_ids)
    {
        $result = $appeals;

        $query_text = '
SELECT COMPANY.ID_COMPANY AS ca_id, COMPANY_NAME AS ca_name
FROM COMPANY
WHERE COMPANY.ID_COMPANY IN (' . $ca_ids . ')';

        $result_array = Yii::$app->db_mssql->createCommand($query_text)->queryAll();
        if (count($result_array) > 0) {
            // дополним массив обращений наименованиями контрагентов
            foreach ($result_array as $row) {
                foreach ($result as $index => $appeal) {
                    if ($appeal['ca_id'] == $row['ca_id']) {
                        $result[$index]['ca_name'] = $row['ca_name'];
                        break;
                    }
                }
            }
        }

        return $result;
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

    /**
     * Делает выборку типов проектов и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @param $logistLimit bool применение идентификаторов, ограничивающих выборку (для логистов)
     * @return array
     */
    public static function arrayMapOfProjectsTypesForSelect2($logistLimit = null)
    {
        return ArrayHelper::map(self::fetchProjectsTypes($logistLimit) , 'id', 'name');
    }

    /**
     * Делает выборку статусов проектов и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @param $logistLimit bool применение идентификаторов, ограничивающих выборку (для логистов)
     * @return array
     */
    public static function arrayMapOfProjectsStatesForSelect2($logistLimit = null)
    {
        return ArrayHelper::map(self::fetchProjectsStates($logistLimit) , 'id', 'name');
    }
}
