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
        return ArrayHelper::map(self::find()->select(['id' => 'region_id', 'name'])->where(['country_id' => 3159])->orderBy('name')->all(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransportRequests()
    {
        return $this->hasMany(TransportRequests::className(), ['region_id' => 'region_id']);
    }
}
