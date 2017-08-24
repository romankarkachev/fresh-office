<?php

namespace common\models;

use Yii;

/**
 * Модель для выборки из таблицы проектов Fresh Office.
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $type_id
 * @property integer $ca_id
 * @property integer $manager_id
 * @property integer $state_id
 * @property float $amount
 * @property float $cost
 * @property string $type_name
 * @property string $ca_name
 * @property string $manager_name
 * @property string $state_name
 * @property string $perevoz
 * @property string $proizodstvo
 * @property string $oplata
 * @property string $adres
 * @property string $dannie
 * @property string $ttn
 * @property string $weight
 * @property string $vivozdate
 * @property string $date_start
 * @property string $date_end
 *
 * @property string $customerName
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
        ];
    }

    /**
     * Функция склонения слов.
     * @param mixed $digit
     * @param mixed $expr
     * @param bool $onlyword
     * @return string
     */
    private static function declension($digit, $expr, $onlyword=false){
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
     * @return string
     */
    public static function downcounter($startPeriod, $endPeriod=false){
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
        if ($hours > 0) $str .= self::declension($hours, ['час','часа','часов']) . ' ';
        if ($minutes > 0) $str .= self::declension($minutes, ['минута','минуты','минут']) . ' ';
        if ($seconds > 0) $str .= self::declension($seconds, ['секунда','секунды','секунд']);

        return $str;
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
}
