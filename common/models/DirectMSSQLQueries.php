<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\db\Expression;

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
     * Набор статусов проектов для отбора логистом
     */
    const PROJECT_STATES_FOR_LOGIST_FILTER = [
        ProjectsStates::STATE_ОПЛАЧЕНО,
        ProjectsStates::STATE_СОГЛАСОВАНИЕ_ВЫВОЗА,
        ProjectsStates::STATE_ВЫВОЗ_ЗАВЕРШЕН,
        ProjectsStates::STATE_ЗАВЕРШЕНО,
        ProjectsStates::STATE_САМОПРИВОЗ_ОДОБРЕН,
        ProjectsStates::STATE_ТРАНСПОРТ_ЗАКАЗАН,
        ProjectsStates::STATE_ЕДЕТ_К_ЗАКАЗЧИКУ,
        ProjectsStates::STATE_У_ЗАКАЗЧИКА,
        ProjectsStates::STATE_ЕДЕТ_НА_СКЛАД,
        ProjectsStates::STATE_НА_СКЛАДЕ,
    ];

    /**
     * Типы проектов для ответственных по типам проектов.
     * фото/видео, выездные работы, осмотр объекта
     */
    const PROJECTS_TYPES_FOR_RESPONSIBLE = [
        ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПРЕДОПЛАТА,
        ProjectsTypes::PROJECT_TYPE_ВЫВОЗ,
        ProjectsTypes::PROJECT_TYPE_ЗАКАЗ_ПОСТОПЛАТА,
        ProjectsTypes::PROJECT_TYPE_САМОПРИВОЗ,
        ProjectsTypes::PROJECT_TYPE_ФОТО_ВИДЕО, // 7
        ProjectsTypes::PROJECT_TYPE_ВЫЕЗДНЫЕ_РАБОТЫ, // 8
        ProjectsTypes::PROJECT_TYPE_ОСМОТР_ОБЪЕКТА, // 14
    ];

    /**
     * Статусы проектов.
     * оплачено, у заказчика, на складе, едет на склад, едет к заказчику, вывоз завершен, вывоз согласован,
     * самопривоз одобрен, согласование вывоза, транспорт заказан
     */
    const PROJECTS_STATES_LOGIST_LIMIT = '5,6,13,28,30,31,32,33,34';

    /**
     * Названия таблиц в MS SQL
     */
    const TABLE_NAME_ПЕРЕВОЗЧИКИ = 'ADD_SPR_perevoznew';

    /**
     * Почтовый адрес.
     */
    const ADDRESS_TYPES_ПОЧТОВЫЙ = 3;

    /**
     * Делает выборку типов проектов.
     * @param $logistLimit bool применение идентификаторов, ограничивающих выборку (для логистов)
     * @return array
     */
    public static function fetchProjectsTypes($logistLimit = null)
    {
        $conditionIds = '';
        if (isset($logistLimit)) $conditionIds = chr(13) . 'WHERE ID_LIST_SPR_PROJECT IN (' . $logistLimit . ')';

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
        if (isset($logistLimit)) $conditionIds = chr(13) . 'WHERE ID_PRIZNAK_PROJECT IN (' . implode(',', self::PROJECT_STATES_FOR_LOGIST_FILTER) . ')';

        $query_text = '
SELECT ID_PRIZNAK_PROJECT AS id, PRIZNAK_PROJECT AS name
FROM CBaseCRM_Fresh_7x.dbo.LIST_SPR_PRIZNAK_PROJECT' . $conditionIds . '
ORDER BY PRIZNAK_PROJECT';

        return Yii::$app->db_mssql->createCommand($query_text)->queryAll();
    }

    /**
     * Возвращает в виде массива менеджеров CRM.
     * @param $forSelect2 bool признак выборки для виджетов select2 (по-другому будет называться поле на выходе)
     * @param $q string (строка запроса, для поиска менеджера по имени)
     * @return array
     */
    public static function fetchManagers($forSelect2 = false, $q = null)
    {
        // определимся, как будет называться поле с наименованием на выходе
        $fieldName = 'name';
        if ($forSelect2 === true) $fieldName = 'text';

        // условие отбора
        $condition = '';
        if (isset($q)) $condition = chr(13) . 'WHERE MANAGER_NAME LIKE \'%' . $q . '%\'';
        $query_text = '
SELECT ID_MANAGER AS id, MANAGER_NAME AS ' . $fieldName . '
FROM CBaseCRM_Fresh_7x.dbo.MANAGERS' . $condition .'
ORDER BY MANAGER_NAME';

        return Yii::$app->db_mssql->createCommand($query_text)->queryAll();
    }

    /**
     * Возвращает данные менеджера.
     * @param $id integer идентификатор менеджера
     * @return array
     */
    public static function fetchManager($id)
    {
        if (intval($id) > 0) {
            $query_text = '
SELECT ID_MANAGER AS id, MANAGER_NAME AS name
FROM CBaseCRM_Fresh_7x.dbo.MANAGERS
WHERE ID_MANAGER=' . intval($id);

            return Yii::$app->db_mssql->createCommand($query_text)->queryAll();
        }

        return [];
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
    1 AS custom,
    COMPANY.ID_COMPANY AS id, COMPANY_NAME AS text,
    MANAGERS.ID_MANAGER AS managerId, MANAGERS.MANAGER_NAME AS managerName
FROM CBaseCRM_Fresh_7x.dbo.COMPANY
LEFT JOIN MANAGERS ON MANAGERS.ID_MANAGER = COMPANY.ID_MANAGER
WHERE
    TRASH = 0
    AND COMPANY.COMPANY_NAME LIKE \'%' . $name . '%\'
ORDER BY COMPANY_NAME';

        return Yii::$app->db_mssql->createCommand($query_text)->queryAll();
    }

    /**
     * Возвращает контактных лиц контрагента.
     * @param $ca_id integer идентификатор контрагента, контактные лица которого извлекаются
     * @return array
     */
    public static function fetchCounteragentsContactPersons($ca_id)
    {
        $query_text = '
SELECT
    LIST_CONTACT_MAN.ID_CONTACT_MAN AS id,
    LTRIM(RTRIM(CONTACT_MAN_NAME)) + \' (\' + ISNULL(LIST_TELEPHONES.PHONES, \'\') + \')\' AS text,
    LTRIM(RTRIM(CONTACT_MAN_NAME)) AS name,
    LTRIM(RTRIM(LIST_TELEPHONES.PHONES)) AS phones
FROM CBaseCRM_Fresh_7x.dbo.LIST_CONTACT_MAN
LEFT JOIN (
	SELECT ID_CONTACT_MAN, STUFF((SELECT \', \' + LTRIM(RTRIM(TELEPHONE)) FROM LIST_TELEPHONES LT WHERE LT.ID_CONTACT_MAN = LIST_TELEPHONES.ID_CONTACT_MAN FOR XML PATH(\'\')), 1, 1, \'\') AS PHONES
	FROM LIST_TELEPHONES
	GROUP BY ID_CONTACT_MAN
) AS LIST_TELEPHONES ON LIST_TELEPHONES.ID_CONTACT_MAN = LIST_CONTACT_MAN.ID_CONTACT_MAN
WHERE
    (TRASH = 0 OR TRASH IS NULL)
    AND ID_COMPANY=' . $ca_id . '
ORDER BY CONTACT_MAN_NAME';

        return Yii::$app->db_mssql->createCommand($query_text)->queryAll();
    }

    /**
     * Возвращает контактное лицо контрагента.
     * @param $id integer идентификатор контрагента, контактные лица которого извлекаются
     * @return array
     */
    public static function fetchContactPerson($id)
    {
        $query_text = '
SELECT
    LTRIM(RTRIM(CONTACT_MAN_NAME)) AS name,
    LTRIM(RTRIM(LIST_TELEPHONES.PHONES)) AS phones
FROM CBaseCRM_Fresh_7x.dbo.LIST_CONTACT_MAN
LEFT JOIN (
	SELECT ID_CONTACT_MAN, STUFF((SELECT \', \' + LTRIM(RTRIM(TELEPHONE)) FROM LIST_TELEPHONES LT WHERE LT.ID_CONTACT_MAN = LIST_TELEPHONES.ID_CONTACT_MAN FOR XML PATH(\'\')), 1, 1, \'\') AS PHONES
	FROM LIST_TELEPHONES
	GROUP BY ID_CONTACT_MAN
) AS LIST_TELEPHONES ON LIST_TELEPHONES.ID_CONTACT_MAN = LIST_CONTACT_MAN.ID_CONTACT_MAN
WHERE
    (TRASH = 0 OR TRASH IS NULL)
    AND LIST_CONTACT_MAN.ID_CONTACT_MAN=' . $id;

        return Yii::$app->db_mssql->createCommand($query_text)->queryAll();
    }

    /**
     * Возвращает полную информацию о проекте.
     * @param $project_id integer идентификатор проекта
     * @return mixed
     */
    public static function fetchProjectsData($project_id)
    {
        $properties = self::getProjectsProperties((string)$project_id);
        $invoice = self::getProjectsInvoices((string)$project_id);

        $query_text = '
SELECT
    LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY AS id,
    LIST_PROJECT_COMPANY.ID_COMPANY AS ca_id, COMPANY.COMPANY_NAME AS ca_name,
    LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT AS state_id, LIST_SPR_PRIZNAK_PROJECT.PRIZNAK_PROJECT AS state_name,
    LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT AS type_id, LIST_SPR_PROJECT.NAME_PROJECT AS type_name,
    ADD_vivozdate AS vivozdate,
    LIST_PROJECT_COMPANY.ID_CONTACT_MAN AS contact_id, LIST_CONTACT_MAN.CONTACT_MAN_NAME AS contact_name, LIST_TELEPHONES.TELEPHONE AS contact_phone,
    LIST_PROJECT_COMPANY.ID_MANAGER_VED AS manager_id, MANAGERS.MANAGER_NAME AS manager_name,
    ADD_perevoz AS ferryman,
    ADD_dannie AS dannie,
    ADD_ttn AS ttn,
    ADD_adres AS address,
    LIST_PROJECT_COMPANY.PRIM_PROJECT_COMPANY AS comment,
    ISNULL(FINANCES.COUNT_FINANCE, 0) AS finance_count,
    payment.amount,
    payment.cost
FROM CBaseCRM_Fresh_7x.dbo.LIST_PROJECT_COMPANY
LEFT JOIN COMPANY ON COMPANY.ID_COMPANY = LIST_PROJECT_COMPANY.ID_COMPANY
LEFT JOIN LIST_SPR_PRIZNAK_PROJECT ON LIST_SPR_PRIZNAK_PROJECT.ID_PRIZNAK_PROJECT = LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT
LEFT JOIN LIST_SPR_PROJECT ON LIST_SPR_PROJECT.ID_LIST_SPR_PROJECT = LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT
LEFT JOIN LIST_CONTACT_MAN ON LIST_CONTACT_MAN.ID_CONTACT_MAN = LIST_PROJECT_COMPANY.ID_CONTACT_MAN
OUTER APPLY (
    SELECT TOP 1 LIST_TELEPHONES.ID_CONTACT_MAN, LIST_TELEPHONES.TELEPHONE FROM LIST_TELEPHONES
    WHERE LIST_TELEPHONES.ID_CONTACT_MAN = LIST_PROJECT_COMPANY.ID_CONTACT_MAN
) LIST_TELEPHONES
LEFT JOIN MANAGERS ON MANAGERS.ID_MANAGER = LIST_PROJECT_COMPANY.ID_MANAGER_VED
LEFT JOIN (
	SELECT ID_LIST_PROJECT_COMPANY, COUNT(ID_MANY) AS COUNT_FINANCE
	FROM LIST_MANYS
	WHERE ID_SUB_PRIZNAK_MANY = ' . FreshOfficeAPI::FINANCES_PAYMENT_SIGN_УТИЛИЗАЦИЯ . ' AND
	      ID_NAPR = ' . FreshOfficeAPI::FINANCES_DIRECTION_ПРИХОД . ' AND
	      MANAGER_TRASH IS NULL
	GROUP BY ID_LIST_PROJECT_COMPANY
) AS FINANCES ON FINANCES.ID_LIST_PROJECT_COMPANY = LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY
LEFT JOIN (
	SELECT ID_LIST_PROJECT_COMPANY, SUM(PRICE_TOVAR * KOLVO) AS amount, SUM(SS_PRICE_TOVAR * KOLVO) AS cost
	FROM CBaseCRM_Fresh_7x.dbo.LIST_TOVAR_PROJECT
	GROUP BY ID_LIST_PROJECT_COMPANY
) AS payment ON payment.ID_LIST_PROJECT_COMPANY = LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY
WHERE LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY = ' . $project_id;

        $result = Yii::$app->db_mssql->createCommand($query_text)->queryAll();
        if (count($result) > 0) {
            $result = $result[0];
            $result['properties'] = $properties;
            $result['tp'] = $invoice;
        }

        return $result;
    }

    /**
     * Делает выборку проектов.
     * @param $projects_types_ids string идентификаторы типов проектов, которые будут отобраны
     * @param $exclude_ids string идентификаторы проектов, которые будут исключены из выборки
     */
    public static function fetchProjectsForMailingByTypes($projects_types_ids, $exclude_ids)
    {
        if ($exclude_ids != '')
            $conditionsExcludeIds = '
    LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY NOT IN (' . $exclude_ids . ')
    AND';

        $query_text = '
SELECT LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY AS id
,LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT AS type_id, LIST_SPR_PROJECT.NAME_PROJECT AS type_name
,LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT AS state_id, LIST_SPR_PRIZNAK_PROJECT.PRIZNAK_PROJECT AS state_name
,DATE_CREATE_PROGECT AS date_created
,DATE_START_PROJECT AS date_start
,DATE_FINAL_PROJECT AS date_end
,LIST_PROJECT_COMPANY.ID_COMPANY AS ca_id, COMPANY.COMPANY_NAME AS ca_name
,LIST_PROJECT_COMPANY.ID_CONTACT_MAN AS contact_id, LIST_CONTACT_MAN.CONTACT_MAN_NAME AS contact_name, LIST_TELEPHONES.TELEPHONE AS contact_phone
,LIST_PROJECT_COMPANY.ID_MANAGER_CREATOR AS author_id, MANAGERS.MANAGER_NAME AS author_name
,LIST_PROJECT_COMPANY.ID_MANAGER_VED AS manager_id, MANAGERS.MANAGER_NAME AS manager_name
,payment.amount
,payment.cost
,LIST_PROJECT_COMPANY.PRIM_PROJECT_COMPANY AS comment
FROM [CBaseCRM_Fresh_7x].[dbo].[LIST_PROJECT_COMPANY]
LEFT JOIN LIST_SPR_PROJECT ON LIST_SPR_PROJECT.ID_LIST_SPR_PROJECT = LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT
LEFT JOIN LIST_SPR_PRIZNAK_PROJECT ON LIST_SPR_PRIZNAK_PROJECT.ID_PRIZNAK_PROJECT = LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT
LEFT JOIN COMPANY ON COMPANY.ID_COMPANY = LIST_PROJECT_COMPANY.ID_COMPANY
LEFT JOIN MANAGERS ON MANAGERS.ID_MANAGER = LIST_PROJECT_COMPANY.ID_MANAGER_VED
LEFT JOIN LIST_CONTACT_MAN ON LIST_CONTACT_MAN.ID_CONTACT_MAN = LIST_PROJECT_COMPANY.ID_CONTACT_MAN
LEFT JOIN LIST_TELEPHONES ON LIST_TELEPHONES.ID_CONTACT_MAN = LIST_PROJECT_COMPANY.ID_CONTACT_MAN
LEFT JOIN (
	SELECT ID_LIST_PROJECT_COMPANY, SUM(PRICE_TOVAR * KOLVO) AS amount, SUM(SS_PRICE_TOVAR * KOLVO) AS cost
	FROM [CBaseCRM_Fresh_7x].[dbo].[LIST_TOVAR_PROJECT]
	GROUP BY ID_LIST_PROJECT_COMPANY
) AS payment ON payment.ID_LIST_PROJECT_COMPANY = LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY
WHERE'. $conditionsExcludeIds . '
    LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT IN (' . $projects_types_ids . ')
    AND LIST_PROJECT_COMPANY.DATE_CREATE_PROGECT > ' . new \yii\db\Expression('CONVERT(datetime, \''. date('Y-m-d', (time() - 7*24*3600)) .'T00:00:00.000\', 126)') . '
ORDER BY LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT';

        $result = Yii::$app->db_mssql->createCommand($query_text)->queryAll();
        return $result;
    }

    /**
     * Делает выборку проектов для представления их в формате PDF.
     * @param $exclude_ids string идентификаторы проектов, которые будут исключены из выборки
     */
    public static function fetchProjectsForMailingPDF($exclude_ids)
    {
        if ($exclude_ids != '')
            $conditionsExcludeIds = '
    LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY NOT IN (' . $exclude_ids . ')
    AND';

        $today = new Expression('CONVERT(datetime, \''. date('Y-m-d', time()) .'T00:00:00.000\', 126)');
        $zwo_days_plus = new Expression('CONVERT(datetime, \''. date('Y-m-d', (time() + 1*24*3600)) .'T23:59:59.000\', 126)');

        $query_text = '
SELECT LIST_PROJECT_COMPANY.ID_LIST_PROJECT_COMPANY AS id,
LIST_PROJECT_COMPANY.ID_COMPANY AS ca_id, COMPANY.COMPANY_NAME AS ca_name,
LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT AS type_id, LIST_SPR_PROJECT.NAME_PROJECT AS type_name,
ADD_vivozdate AS vivozdate,
LIST_PROJECT_COMPANY.ID_CONTACT_MAN AS contact_id, LIST_CONTACT_MAN.CONTACT_MAN_NAME AS contact_name, LIST_TELEPHONES.TELEPHONE AS contact_phone,
LIST_PROJECT_COMPANY.ID_MANAGER_VED AS manager_id, MANAGERS.MANAGER_NAME AS manager_name,
ADD_perevoz AS ferryman,
ADD_dannie AS dannie,
LIST_PROJECT_COMPANY.PRIM_PROJECT_COMPANY AS comment
FROM CBaseCRM_Fresh_7x.dbo.LIST_PROJECT_COMPANY
LEFT JOIN COMPANY ON COMPANY.ID_COMPANY = LIST_PROJECT_COMPANY.ID_COMPANY
LEFT JOIN LIST_SPR_PROJECT ON LIST_SPR_PROJECT.ID_LIST_SPR_PROJECT = LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT
LEFT JOIN LIST_CONTACT_MAN ON LIST_CONTACT_MAN.ID_CONTACT_MAN = LIST_PROJECT_COMPANY.ID_CONTACT_MAN
OUTER APPLY (
    SELECT TOP 1 LIST_TELEPHONES.ID_CONTACT_MAN, LIST_TELEPHONES.TELEPHONE FROM LIST_TELEPHONES
    WHERE LIST_TELEPHONES.ID_CONTACT_MAN = LIST_PROJECT_COMPANY.ID_CONTACT_MAN
) LIST_TELEPHONES
LEFT JOIN MANAGERS ON MANAGERS.ID_MANAGER = LIST_PROJECT_COMPANY.ID_MANAGER_VED
WHERE' . $conditionsExcludeIds . '
    LIST_PROJECT_COMPANY.ID_PRIZNAK_PROJECT = ' . ProjectsStates::STATE_ТРАНСПОРТ_ЗАКАЗАН . '
    AND LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT IN (' . implode(',', ResponsibleByProjectTypes::PROJECT_TYPES_PDF) . ')
    AND ADD_vivozdate BETWEEN ' . $today . ' AND ' . $zwo_days_plus . '
    AND (ADD_proizodstvo = \'Ступино\' OR ADD_proizodstvo = \'Воскресенск\' OR ADD_proizodstvo = \'Рошаль\' OR ADD_proizodstvo = \'Потресово\')
ORDER BY LIST_PROJECT_COMPANY.ID_LIST_SPR_PROJECT';

        $result = Yii::$app->db_mssql->createCommand($query_text)->queryAll();
        return $result;
    }

    /**
     * НЕ ИСПОЛЬЗУЕТСЯ НИГДЕ!
     * Делает выборку проектов для списка.
     * @param $queryParams array массив параметров для отбора
     * @return ArrayDataProvider
     */
    public static function fetchProjectsList($queryParams)
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
	SELECT ID_LIST_PROJECT_COMPANY, SUM(PRICE_TOVAR * KOLVO) AS amount, SUM(SS_PRICE_TOVAR * KOLVO) AS cost
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
            'modelClass' => 'common\models\foProjects',
            'key' => 'id',
            'allModels' => $result,
            'pagination' => [
                //'pageSize' => $this->searchPerPage,
            ],
            'sort' => [
                'defaultOrder' => ['date_start' => SORT_DESC],
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
     * Делает выборку почтовых адресов контрагентов
     * @return array
     */
    public static function fetchCounteragentsPostAddresses()
    {
        $query_text = '
SELECT [ID_LIST_ADD_ADDRESS_COMPANY] AS src_id
      ,[ID_COMPANY] AS counteragent_id
      ,[ADD_ADDRESS_COMPANY] AS src_address
      ,[PRIM_ADDRESS_COMPANY] AS comment
FROM [CBaseCRM_Fresh_7x].[dbo].[LIST_ADD_ADDRESS_COMPANY]
WHERE ID_TIP_ADDRESS=' . self::ADDRESS_TYPES_ПОЧТОВЫЙ . '
ORDER BY ID_COMPANY';

        return Yii::$app->db_mssql->createCommand($query_text)->queryAll();
    }

    /**
     * Выполняет выборку параметров проектов, переданных в параметрах.
     * @param $ids string строка с идентификаторами проектов, по которым будет выполнена выборка
     * @return array
     */
    public static function getProjectsProperties($ids)
    {
        if (is_string($ids)) {
            $query_text = '
SELECT ID_LIST_PROJECT_COMPANY AS project_id, PROPERTIES_PROGECT AS property, VALUES_PROPERTIES_PROGECT AS value
FROM CBaseCRM_Fresh_7x.dbo.LIST_PROPERTIES_PROGECT_COMPANY
WHERE
    ID_LIST_PROJECT_COMPANY IN (' . $ids . ') AND
    VALUES_PROPERTIES_PROGECT IS NOT NULL
ORDER BY ID_LIST_PROJECT_COMPANY';

            return Yii::$app->db_mssql->createCommand($query_text)->queryAll();
        }
        else
            return [];
    }

    /**
     * Выполняет выборку товаров проектов, переданных в параметрах.
     * @param $ids string строка с идентификаторами проектов, по которым будет выполнена выборка
     * @return array
     */
    public static function getProjectsInvoices($ids)
    {
        if ($ids != null) {
            $query_text = '
SELECT LIST_DOCUMENTS.ID_LIST_PROJECT_COMPANY AS project_id, ID_TOVAR_DOC
      ,LIST_TOVAR_DOC.ID_DOC
      ,TOVAR_DOC AS property
      ,KOL_VO AS value
      ,[PRICE]
      ,LIST_TOVAR_DOC.SUMMA
      ,[ED_IZM_TOVAR]
      ,[DISCOUNT]
      ,[NDS]
      ,[ID_TOVAR]
      ,[UNITID]
FROM CBaseCRM_Fresh_7x.dbo.LIST_TOVAR_DOC
LEFT JOIN LIST_DOCUMENTS ON LIST_DOCUMENTS.ID_DOC=LIST_TOVAR_DOC.ID_DOC
WHERE LIST_TOVAR_DOC.ID_DOC IN (
	SELECT ID_DOC
	FROM CBaseCRM_Fresh_7x.dbo.LIST_DOCUMENTS
	WHERE ID_LIST_PROJECT_COMPANY IN (' . $ids . ')
)
ORDER BY LIST_DOCUMENTS.ID_LIST_PROJECT_COMPANY';

            return Yii::$app->db_mssql->createCommand($query_text)->queryAll();
        }
        else
            return [];
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
     * Выполняет изменение перевозчика в проекте, а также водителя и транспортного средства.
     * Примеры запросов, которые выполняются:
     * UPDATE [CBaseCRM_Fresh_7x].[dbo].[LIST_PROJECT_COMPANY] SET [ADD_perevoz_new]='1', [ADD_dannie]='TATA г/н H505PK61 Зашкваров Рефат, паспорт 61 № 111222 выдан 16.10.2014 УФМС России по Пермскому краю, вод. удост. 77 05 097264 01.10.2009' WHERE [ID_COMPANY] IN ('12221', '12498', '12561', '12592')
     * @param $project_ids string идентификаторы проектов
     * @param $ferryman_id integer идентификатор перевозчика, который назначается
     * @param $data string данные водителя и транспортного средства
     * @return bool
     */
    public static function assignFerryman($project_ids, $ferryman_id, $data)
    {
        $rows_affected = Yii::$app->db_mssql->createCommand()->update('CBaseCRM_Fresh_7x.dbo.LIST_PROJECT_COMPANY', [
            'ID_PRIZNAK_PROJECT' => FreshOfficeAPI::PROJECT_STATE_ТРАНСПОРТ_ЗАКАЗАН, // статус "Транспорт заказан"
            'ADD_perevoz_new' => $ferryman_id, // наименование перевозчика
            'ADD_dannie' => $data, // данные автомобиля и водителя (тип, марка и номер авто, фио, паспорт и права водителя)
        ], [
            'ID_LIST_PROJECT_COMPANY' => $project_ids,
        ])->execute();
        //])->getRawSql();var_dump($rows_affected);

        if ($rows_affected >= 0)
            return true;
        else
            return false;
    }

    /**
     * Выполняет изменение ответственного лица по контрагенту.
     * Примеры запросов, которые выполняются:
     * UPDATE [CBaseCRM_Fresh_7x].[dbo].[COMPANY] SET [ID_MANAGER]=70 WHERE [ID_COMPANY] IN ('3018', '1')
     * UPDATE [CBaseCRM_Fresh_7x].[dbo].[COMPANY] SET [ID_MANAGER]=70 WHERE [ID_COMPANY]='3018'
     * @param $ca_id integer|string идентификатор(ы) контрагента(ов)
     * @param $manager_id integer идентификатор ответственного лица, которое назначается
     * @return bool
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
     * Выполняет удаление контрагентов с идентификаторами, переданными в параметрах.
     * Примеры запросов:
     * UPDATE [CBaseCRM_Fresh_7x].[dbo].[COMPANY] SET [TRASH]=1 WHERE [ID_COMPANY]='3312'
     * UPDATE [CBaseCRM_Fresh_7x].[dbo].[COMPANY] SET [TRASH]=1 WHERE [ID_COMPANY] IN ('3251', '9', '3312')
     * @param $ca_ids string идентификаторы контрагентов, которые будут удалены
     * @return bool
     */
    public static function deleteCustomers($ca_ids)
    {
        $rows_affected = Yii::$app->db_mssql->createCommand()->update('CBaseCRM_Fresh_7x.dbo.COMPANY', [
            'TRASH' => 1,
        ], [
            'ID_COMPANY' => $ca_ids,
        ])->execute();
        //])->getRawSql();var_dump($rows_affected);

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
	SELECT ID_LIST_PROJECT_COMPANY, SUM(PRICE_TOVAR * KOLVO) AS amount, SUM(SS_PRICE_TOVAR * KOLVO) AS cost
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
     * Выполняет создание перевозчика.
     * @param $model Ferrymen
     * @return bool
     */
    public static function createFerryman($model)
    {
        $rows_affected = Yii::$app->db_mssql->createCommand()->insert('CBaseCRM_Fresh_7x.dbo.' . self::TABLE_NAME_ПЕРЕВОЗЧИКИ, [
            'NAME' => $model->name,
        ])->execute();

        if ($rows_affected > 0) {
            $model->fo_id = Yii::$app->db_mssql->getLastInsertID();
            $model->save();
            return true;
        }
        else
            return false;
    }

    /**
     * Выполняет обновление записей перевозчика.
     * @param $model Ferrymen
     * @return bool
     */
    public static function updateFerryman($model)
    {
        $rows_affected = Yii::$app->db_mssql->createCommand()->update('CBaseCRM_Fresh_7x.dbo.' . self::TABLE_NAME_ПЕРЕВОЗЧИКИ, [
            'NAME' => $model->name,
        ], [
            'ID' => intval($model->fo_id),
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
     * Выполняет идентификацию контрагента по режиму и значению из параметров.
     * @param $value string значение для поиска
     * @param $mode string режим поиска (email, name, phone)
     * @return mixed
     */
    public static function tryToIdentifyCounteragent($value, $mode = 'email')
    {
        switch ($mode) {
            case 'email':
                $query_text = '
SELECT DISTINCT COMPANY.ID_COMPANY AS caId, COMPANY.COMPANY_NAME AS caName,
             MANAGERS.ID_MANAGER AS managerId, MANAGERS.MANAGER_NAME AS managerName,
             STUFF((SELECT \', \' + email FROM LIST_EMAIL_CLIENT LEC WHERE LEC.ID_COMPANY = LIST_EMAIL_CLIENT.ID_COMPANY FOR XML PATH(\'\')), 1, 1, \'\') AS contact
FROM CBaseCRM_Fresh_7x.dbo.LIST_EMAIL_CLIENT
LEFT JOIN COMPANY ON COMPANY.ID_COMPANY = LIST_EMAIL_CLIENT.ID_COMPANY
LEFT JOIN MANAGERS ON MANAGERS.ID_MANAGER = COMPANY.ID_MANAGER
WHERE email LIKE \'%' . $value . '%\' AND COMPANY_NAME IS NOT NULL
ORDER BY COMPANY_NAME';
                $result = Yii::$app->db_mssql->createCommand($query_text)->queryAll();
                if (count($result) > 0) return $result[0];
                break;
        }

        return false;
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
