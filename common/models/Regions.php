<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "region".
 *
 * @property integer $region_id
 * @property integer $country_id
 * @property integer $city_id
 * @property string $name
 *
 * @property TransportRequests[] $transportRequests
 */
class Regions extends \yii\db\ActiveRecord
{
    const COUNTRY_RUSSIA_ID = 3159;

    /**
     * Поле для хранения виртуального значения идентификатора региона.
     * @var integer
     */
    public $id;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'region';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['country_id', 'city_id'], 'integer'],
            [['name'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'region_id' => 'Регион',
            'country_id' => 'Страна',
            'city_id' => 'Город',
            'name' => 'Наименование',
        ];
    }

    /**
     * Делает выборку регионов России и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOnlyRussiaForSelect2()
    {
        return ArrayHelper::map(self::find()->select(['id' => 'region_id', 'name'])->where(['country_id' => self::COUNTRY_RUSSIA_ID])->orderBy('name')->all(), 'id', 'name');
    }

    /**
     * Делает выборку регионов России и возвращает в виде массива, в котором поиск удобно осуществлять по наименованию.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOnlyRussiaForSearchByName()
    {
        return ArrayHelper::map(self::find()->where(['country_id' => self::COUNTRY_RUSSIA_ID])->all(), 'name', 'region_id');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransportRequests()
    {
        return $this->hasMany(TransportRequests::className(), ['region_id' => 'region_id']);
    }
}
