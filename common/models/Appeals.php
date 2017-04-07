<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "appeals".
 *
 * @property integer $id
 * @property integer $created_at
 * @property string $form_username
 * @property string $form_region
 * @property string $form_phone
 * @property string $form_email
 * @property string $form_message
 * @property integer $fo_id_company
 * @property string $fo_company_name
 * @property integer $ca_state_id
 */
class Appeals extends \yii\db\ActiveRecord
{
    /**
     * Новый клиент.
     */
    const CA_STATE_NEW = 0;

    /**
     * Действующий клиент
     */
    const CA_STATE_ACTUAL = 1;

    /**
     * Ответственный по контрагенту.
     * @var integer
     */
    public $fo_id_manager;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'appeals';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'fo_id_company', 'ca_state_id'], 'integer'],
            [['form_message'], 'string'],
            [['form_username', 'form_region', 'form_phone', 'form_email', 'fo_company_name'], 'string', 'max' => 150],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Дата и время создания',
            // поля формы (5):
            'form_username' => 'Имя',
            'form_region' => 'Регион',
            'form_phone' => 'Телефон',
            'form_email' => 'Email',
            'form_message' => 'Текст сообщения',
            'fo_id_company' => 'Контрагент из Fresh Office',
            'fo_company_name' => 'Контрагент', // Наименование контрагента из Fresh Office
            'fo_id_manager' => 'Ответственный', // поле виртуальное, в базе не хранится
            'ca_state_id' => 'Статус контрагента', // 0 - новый, 1 - действующий (определяется по наличию оплаты)
            'caStateName' => 'Статус клиента',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
        ];
    }

    /**
     * Возвращает в виде массива менеджеров CRM.
     * @return array
     */
    public static function fetchManagers()
    {
        $query_text = 'SELECT ID_MANAGER AS id, MANAGER_NAME AS name FROM CBaseCRM_Fresh_7x.dbo.MANAGERS ORDER BY name';
        return Yii::$app->db_mssql->createCommand($query_text)->queryAll();
    }

    /**
     * Возвращает в виде массива разновидности статусов клиентов.
     * @return array
     */
    public static function fetchCaStates()
    {
        return [
            [
                'id' => self::CA_STATE_NEW,
                'name' => 'Новый',
            ],
            [
                'id' => self::CA_STATE_ACTUAL,
                'name' => 'Действующий',
            ],
        ];
    }

    /**
     * Делает выборку статусов клиентов и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfCaStatesForSelect2()
    {
        return ArrayHelper::map(self::fetchCaStates() , 'id', 'name');
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
     * Пытается идентифицировать контрагента по имеющимся в модели контактным данным.
     * @return array
     */
    public function tryToIdentifyCounteragent()
    {
        // идентификация по наименованию
        $query_text = '
SELECT TOP 1 COMPANY.ID_COMPANY AS caId, COMPANY_NAME AS caName,
             MANAGERS.ID_MANAGER AS managerId, MANAGERS.MANAGER_NAME AS managerName,
             ISNULL(COUNT_FINANCE, 0) AS financeCount,
             (CASE WHEN COUNT_FINANCE = 0 THEN ' . self::CA_STATE_NEW . ' ELSE ' . self::CA_STATE_ACTUAL . ' END) AS stateId
FROM CBaseCRM_Fresh_7x.dbo.COMPANY
LEFT JOIN MANAGERS ON MANAGERS.ID_MANAGER = COMPANY.ID_MANAGER
LEFT JOIN (
	SELECT ID_COMPANY, COUNT(ID_MANY) AS COUNT_FINANCE
	FROM LIST_MANYS
	WHERE ID_SUB_PRIZNAK_MANY = ' . Report1::FO_PAYMENT_SIGN_UTILIZATION . ' AND ID_NAPR = 1
	GROUP BY ID_COMPANY
) AS FINANCES ON FINANCES.ID_COMPANY = COMPANY.ID_COMPANY
WHERE COMPANY_NAME LIKE \'%' . trim($this->form_username) . '%\'';
        // пока что так, потому что если имя задано Виктория, находит контрагента с таким наименованием, а это неверно
        //$result = Yii::$app->db_mssql->createCommand($query_text)->queryAll();
        //if (count($result) > 0) return $result;

        // идентификация по номеру телефона
        // в номере телефона убираются восьмерка в начале или семерка при достаточном количестве символов
        $phone_ready = $this->form_phone;
        $phone_ready = preg_replace("/[^0-9]/", '', $phone_ready);
        if (strlen($phone_ready) == 11)
            if ($phone_ready[0] == 7 || $phone_ready[0] == 8)
                $phone_ready = substr($phone_ready, 1);
        $query_text = '
SELECT LIST_TELEPHONES.ID_COMPANY AS caId, COMPANY.COMPANY_NAME AS caName,
             MANAGERS.ID_MANAGER AS managerId, MANAGERS.MANAGER_NAME AS managerName,
             TELEPHONE,
             ISNULL(COUNT_FINANCE, 0) AS financeCount,
             (CASE WHEN ISNULL(COUNT_FINANCE, 0) = 0 THEN ' . self::CA_STATE_NEW . ' ELSE ' . self::CA_STATE_ACTUAL . ' END) AS stateId
FROM CBaseCRM_Fresh_7x.dbo.LIST_TELEPHONES
LEFT JOIN COMPANY ON COMPANY.ID_COMPANY = LIST_TELEPHONES.ID_COMPANY
LEFT JOIN MANAGERS ON MANAGERS.ID_MANAGER = COMPANY.ID_MANAGER
LEFT JOIN (
	SELECT ID_COMPANY, COUNT(ID_MANY) AS COUNT_FINANCE
	FROM LIST_MANYS
	WHERE ID_SUB_PRIZNAK_MANY = ' . Report1::FO_PAYMENT_SIGN_UTILIZATION . ' AND ID_NAPR = 1
	GROUP BY ID_COMPANY
) AS FINANCES ON FINANCES.ID_COMPANY = LIST_TELEPHONES.ID_COMPANY
WHERE TELEPHONE LIKE \'%' . $phone_ready . '%\'';
        $result = Yii::$app->db_mssql->createCommand($query_text)->queryAll();
        if (count($result) > 0) return $result;

        // идентификация по email
        $query_text = '
SELECT COMPANY.ID_COMPANY AS caId, COMPANY.COMPANY_NAME AS caName,
             MANAGERS.ID_MANAGER AS managerId, MANAGERS.MANAGER_NAME AS managerName,
             email,
             ISNULL(COUNT_FINANCE, 0) AS financeCount,
             (CASE WHEN ISNULL(COUNT_FINANCE, 0) = 0 THEN ' . self::CA_STATE_NEW . ' ELSE ' . self::CA_STATE_ACTUAL . ' END) AS stateId
FROM CBaseCRM_Fresh_7x.dbo.LIST_EMAIL_CLIENT
LEFT JOIN COMPANY ON COMPANY.ID_COMPANY = LIST_EMAIL_CLIENT.ID_COMPANY
LEFT JOIN MANAGERS ON MANAGERS.ID_MANAGER = COMPANY.ID_MANAGER
LEFT JOIN (
	SELECT ID_COMPANY, COUNT(ID_MANY) AS COUNT_FINANCE
	FROM LIST_MANYS
	WHERE ID_SUB_PRIZNAK_MANY = ' . Report1::FO_PAYMENT_SIGN_UTILIZATION . ' AND ID_NAPR = 1
	GROUP BY ID_COMPANY
) AS FINANCES ON FINANCES.ID_COMPANY = LIST_EMAIL_CLIENT.ID_COMPANY
WHERE email LIKE \'%' . trim($this->form_email) . '%\'';
        $result = Yii::$app->db_mssql->createCommand($query_text)->queryAll();
        if (count($result) > 0) return $result;

        // ничего найти не удалось
        return [];
    }

    /**
     * Выполняет передачу контрагента от одного менеджера к другому.
     * При этом выполняется соответствующий update-запрос к базе данных SQL.
     * @param $ca_id integer идентификатор контрагента, который передается
     * @param $manager_id integer идентификатор менеджера-получателя контрагента
     */
    public static function delegateCounteragent($ca_id, $manager_id)
    {
        Yii::$app->db_mssql->createCommand()->update('CBaseCRM_Fresh_7x.dbo.COMPANY', [
            'ID_MANAGER' => $manager_id,
        ], [
            'ID_COMPANY' => intval($ca_id),
        ])->execute();
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
     * Получает текущего ответственного по контрагенту из базы данных CRM.
     * @return array
     */
    public function getCounteragentsReliable()
    {
        $query_text = '
SELECT MANAGERS.ID_MANAGER AS id, MANAGERS.MANAGER_NAME AS name
FROM CBaseCRM_Fresh_7x.dbo.COMPANY
LEFT JOIN MANAGERS ON MANAGERS.ID_MANAGER = COMPANY.ID_MANAGER
WHERE COMPANY.ID_COMPANY = ' . $this->fo_id_company;
        return Yii::$app->db_mssql->createCommand($query_text)->queryAll();
    }

    /**
     * Возвращает идентификатор или наименование ответственного в зависимости от значения параметра.
     * @param $field string
     * @return string
     */
    public function getCounteragentsReliableField($field = 'id')
    {
        $sourceTable = $this->getCounteragentsReliable();
        if (count($sourceTable) > 0)
            return $sourceTable[0][$field];
        else
            return '';
    }

    /**
     * Возвращает наименование статуса клиента.
     * @param $state_id integer|null идентификатор статуса, для которого нужно определить наименование
     * @return string
     */
    public function getCaStateName($state_id = null)
    {
        // если не передается снаружи, возьмем значение поля модели
        if ($state_id === null) {
            $state_id = $this->ca_state_id;
        }

        if (null === $state_id) {
            return '<не определен>';
        }

        $sourceTable = self::fetchCaStates();
        $key = array_search($state_id, array_column($sourceTable, 'id'));
        if (false !== $key)
            return $sourceTable[$key]['name'];
        else
            return '';
    }
}
