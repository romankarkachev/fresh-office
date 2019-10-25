<?php

namespace common\models;

use Yii;

/**
 * Модель для выборки из таблицы проектов Fresh Office.
 *
 * @property integer $id [ID_LIST_PROJECT_COMPANY]
 * @property integer $created_at [DATE_CREATE_PROGECT]
 * @property integer [ID_CONTACT_MAN] контактное лицо
 * @property integer $type_id поле [ID_LIST_SPR_PROJECT], таблица [LIST_SPR_PROJECT]
 * @property integer $ca_id [ID_COMPANY]
 * @property integer $manager_id [ID_MANAGER]
 * @property integer $state_id поле [ID_PRIZNAK_PROJECT], таблица [LIST_SPR_PRIZNAK_PROJECT]
 * @property float $amount
 * @property float $cost
 * @property string $type_name
 * @property string $ca_name
 * @property string $manager_name
 * @property string $state_name
 * @property string $perevoz [ADD_perevoz]
 * @property string $proizodstvo [ADD_proizodstvo]
 * @property string $oplata
 * @property string $adres [ADD_adres]
 * @property string $dannie [ADD_dannie]
 * @property string $ttn
 * @property string $weight
 * @property string $vivozdate [ADD_vivozdate]
 * @property string $date_start
 * @property string $date_end
 * @property string [PRIM_PROJECT_COMPANY]
 * @property float $ADD_vol_ttn
 * @property float $ADD_weight_true
 * @property float $ADD_vol_fact
 * @property float $ADD_ttn
 *
 * @property string $customerName вычисляется безобразно, но эффективно
 * @property string $companyName вычисляется при помощи relation
 * @property string $companyInn ИНН контрагента
 * @property string $companyManagerId ответственный за контрагента
 * @property string $companyManagerName ответственный за контрагента
 * @property string $companyManagerCreatorEmailValue E-mail ответственного за контрагента
 * @property string $contactPersonName имя контактного лица
 * @property string $paramAddressValue
 *
 * @property foCompany $company
 * @property foManagers $companyManager
 * @property foCompanyContactPersons $contactPerson
 * @property foProjectsParameters $paramAddress
 */
class foProjects extends \yii\db\ActiveRecord
{
    public $isNewRecord;

    public $id;
    public $created_at;
    public $type_id;
    public $ca_id;
    public $manager_id;
    public $state_id;
    public $amount;
    public $cost;
    public $type_name;
    public $ca_name;
    public $manager_name;
    public $state_name;
    public $perevoz;
    public $proizodstvo;
    public $oplata;
    public $adres;
    public $dannie;
    public $ttn;
    public $weight;
    public $vivozdate;
    public $date_start;
    public $date_end;

    /**
     * Количество финансовых движений по проекту (каких именно зависит от запроса).
     * @var integer
     */
    public $financesCount;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'CBaseCRM_Fresh_7x.dbo.LIST_PROJECT_COMPANY';
    }

    /**
     * @inheritdoc
     */
    public static function primaryKey()
    {
        return ['ID_LIST_PROJECT_COMPANY'];
    }

    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        return Yii::$app->db_mssql;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type_name', 'ca_name', 'manager_name', 'state_name', 'perevoz', 'proizodstvo', 'oplata', 'adres', 'dannie', 'ttn', 'weight'], 'string'],
            [['id', 'created_at', 'type_id', 'ca_id', 'manager_id', 'state_id'], 'integer'],
            [['amount', 'cost'], 'number'],
            [['vivozdate', 'date_start', 'date_end'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Создан',
            'type_id' => 'Тип',
            'state_id' => 'Статус',
            'manager_id' => 'Менеджер',
            'ca_id' => 'Контрагент',
            'amount' => 'Стоимость',
            'cost' => 'Себестоимость',
            'vivozdate' => 'Дата вывоза',
            'date_start' => 'Начало',
            'date_end' => 'Завершение',
            'perevoz' => 'Перевозчик',
            'proizodstvo' => 'Произв. площ.',
            'oplata' => 'Дата оплаты',
            'adres' => 'Адрес',
            'dannie' => 'Данные',
            'ttn' => 'ТТН',
            'weight' => 'Вес',
            'type_name' => 'Тип',
            'state_name' => 'Статус',
            'manager_name' => 'Менеджер',
            'ca_name' => 'Контрагент',
            // поля для электронной очереди транспорта, следующего на склад
            'state_acquired_at' => 'Уехал',
            'address' => 'Адрес',
            'destination' => 'Площадка',
            'data' => 'Данные',
            'ferryman' => 'Перевозчик',
            'remain_text' => 'Время в пути',
            'arriving_at' => 'Время прибытия',
            'unload_at' => 'Разгрузка',
        ];
    }

    /**
     * Функция склонения слов.
     * @param mixed $digit
     * @param mixed $expr
     * @param bool $onlyword
     * @return string
     */
    public static function declension($digit, $expr, $onlyword=false) {
        if (!is_array($expr)) $expr = array_filter(explode(' ', $expr));
        if (empty($expr[2])) $expr[2] = $expr[1];
        $i = preg_replace('/[^0-9]+/s', '', $digit) % 100;
        if ($onlyword) $digit='';
        if ($i >= 5 && $i <= 20)
            $res = $digit . ' ' . $expr[2];
        else {
            $i %= 10;
            if($i == 1) $res=$digit . ' ' . $expr[0];
            elseif ($i>=2 && $i<=4) $res = $digit . ' ' . $expr[1];
            else $res = $digit . ' ' . $expr[2];
        }
        return trim($res);
    }

    /**
     * Счетчик обратного отсчета. Возвращает количество дней, часов, минут и секунд, прошедшее от значения в
     * первом параметре до значения во втором параметре.
     * @param $startPeriod integer начало периода в формате TIMESTAMP
     * @param $endPeriod integer конец периода в формате TIMESTAMP
     * @param $onlyDays bool признак, позволяющий возвращать только количество дней
     *
     * @return string
     */
    public static function downcounter($startPeriod, $endPeriod=false, $onlyDays=false) {
        $startPeriod = intval($startPeriod);
        $check_time = $startPeriod;
        if ($endPeriod !== false) {
            $endPeriod = intval($endPeriod);
            $check_time = $endPeriod - $startPeriod;
        }

        if ($check_time <= 0) {
            return '-';
        }

        $days = floor($check_time/86400);
        $hours = floor(($check_time % 86400)/3600);
        $minutes = floor(($check_time%3600)/60);
        $seconds = $check_time%60;

        $str = '';
        if ($days > 0) $str .= self::declension($days, ['день','дня','дней']) . ' ';
        if ($hours > 0 && !$onlyDays) $str .= self::declension($hours, ['час','часа','часов']) . ' ';
        if ($minutes > 0 && !$onlyDays) $str .= self::declension($minutes, ['минута','минуты','минут']) . ' ';
        if ($seconds > 0 && !$onlyDays) $str .= self::declension($seconds, ['секунда','секунды','секунд']);

        return $str;
    }

    /**
     * Создает запись в истории изменения статусов проекта в CRM.
     * @param $project_id integer идентификатор проекта
     * @param $state_id integer идентификатор нового статуса проекта
     * @param $oldStateName string наименование старого статуса
     * @param $newStateName string наименование нового статуса
     */
    public static function createHistoryRecord($project_id, $state_id, $oldStateName, $newStateName)
    {
        $historyModel = new foProjectsHistory();
        $historyModel->ID_LIST_PROJECT_COMPANY = $project_id;
        $historyModel->ID_MANAGER = 73; // freshoffice
        $historyModel->DATE_CHENCH_PRIZNAK = date('Y-m-d\TH:i:s.000');
        $historyModel->TIME_CHENCH_PRIZNAK = Yii::$app->formatter->asDate(time(), 'php:H:i');
        $historyModel->ID_PRIZNAK_PROJECT = $state_id;
        $historyModel->RUN_NAME_CHANCH = 'Изменен статус проeкта c ' . $oldStateName . ' на ' . $newStateName;
        $historyModel->save();
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes){
        parent::afterSave($insert, $changedAttributes);

        if (isset($changedAttributes['ID_PRIZNAK_PROJECT'])) {
            // проверим, отличается ли текущий статус от нового
            if ($changedAttributes['ID_PRIZNAK_PROJECT'] != $this->ID_PRIZNAK_PROJECT) {
                // определим наименования статусов старого и нового
                $oldStateName = '';
                $newStateName = '';
                $states = DirectMSSQLQueries::fetchProjectsStates();
                if (count($states) > 0) {
                    // старое наименование статуса
                    $key = array_search($changedAttributes['ID_PRIZNAK_PROJECT'], array_column($states, 'id'));
                    if (false !== $key) $oldStateName = $states[$key]['name'];

                    // новое наименование статуса
                    $key = array_search($this->ID_PRIZNAK_PROJECT, array_column($states, 'id'));
                    if (false !== $key) $newStateName = $states[$key]['name'];

                    if ($oldStateName != null && $newStateName != null) {
                        // если наименования обоих статусов определить удалось, то создаем запись в истории изменения статусов
                        self::createHistoryRecord($this->ID_LIST_PROJECT_COMPANY, $this->ID_PRIZNAK_PROJECT, $oldStateName, $newStateName);
                    }
                }
            }
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(foCompany::className(), ['ID_COMPANY' => 'ID_COMPANY']);
    }

    /**
     * Возвращает наименование контрагента.
     * @return string
     */
    public function getCompanyName()
    {
        return !empty($this->company) ? $this->company->COMPANY_NAME : '';
    }

    /**
     * Возвращает ИНН контрагента.
     * @return string
     */
    public function getCompanyInn()
    {
        return !empty($this->company) ? $this->company->INN : '';
    }

    /**
     * @return yii\db\ActiveQuery
     */
    public function getCompanyManager()
    {
        return $this->hasOne(foManagers::class, ['ID_MANAGER' => 'ID_MANAGER'])->via('company');
    }

    /**
     * Возвращает идентификатор ответственного за контрагента.
     * @return string
     */
    public function getCompanyManagerId()
    {
        return !empty($this->company) ? $this->company->ID_MANAGER : '';
    }

    /**
     * Возвращает имя ответственного за контрагента.
     * @return string
     */
    public function getCompanyManagerName()
    {
        return !empty($this->companyManager) ? $this->companyManager->MANAGER_NAME : '';
    }

    /**
     * @return yii\db\ActiveQuery
     */
    public function getCompanyManagerCreator()
    {
        return $this->hasOne(foManagers::class, ['ID_MANAGER' => 'ID_MANAGER_CREATOR']);
    }

    /**
     * Возвращает E-mail ответственного за контрагента.
     * @return string
     */
    public function getCompanyManagerCreatorEmailValue()
    {
        return !empty($this->companyManagerCreator) ? $this->companyManagerCreator->e_mail : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContactPerson()
    {
        return $this->hasOne(foCompanyContactPersons::className(), ['ID_CONTACT_MAN' => 'ID_CONTACT_MAN']);
    }

    /**
     * Возвращает имя контактного лица.
     * @return string
     */
    public function getContactPersonName()
    {
        return !empty($this->contactPerson) ? $this->contactPerson->CONTACT_MAN_NAME : '';
    }

    /**
     * @return yii\db\ActiveQuery
     */
    public function getContactPersonEmail()
    {
        return $this->hasOne(foListEmailClient::class, ['ID_COMPANY' => 'ID_COMPANY', 'ID_CONTACT_MAN' => 'ID_CONTACT_MAN']);
    }

    /**
     * Возвращает E-mail контактного лица.
     * @return string
     */
    public function getContactPersonEmailValue()
    {
        return !empty($this->contactPersonEmail) ? $this->contactPersonEmail->email : '';
    }

    /**
     * Делает запрос с целью установления наименования контрагента по имеющемуся идентификатору.
     * @return string
     */
    public function getCustomerName()
    {
        if ($this->ca_id == null) return '';

        $ca = DirectMSSQLQueries::fetchCounteragent($this->ca_id);
        if (is_array($ca)) if (count($ca) > 0) return $ca[0]['caName'];
        return '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParamAddress()
    {
        return $this->hasOne(foProjectsParameters::className(), ['ID_LIST_PROJECT_COMPANY' => 'ID_LIST_PROJECT_COMPANY'])->andWhere(['PROPERTIES_PROGECT' => 'Адрес']);
    }

    /**
     * Возвращает значение адреса из параметров проекта.
     * @return string
     */
    public function getParamAddressValue()
    {
        return !empty($this->paramAddress) ? $this->paramAddress->VALUES_PROPERTIES_PROGECT : '';
    }
}
