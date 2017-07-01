<?php

namespace common\models;

use Yii;

/**
 * Модель для выборки из таблицы проектов Fresh Office.
 *
 * @property integer $id
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
 */
class foProjects extends \yii\db\ActiveRecord
{
    public $isNewRecord;

    public $id;
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
            [['id', 'type_id', 'ca_id', 'manager_id', 'state_id'], 'integer'],
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
}
