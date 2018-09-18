<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "projects".
 *
 * @property integer $id
 * @property integer $created_at
 * @property string $address
 * @property string $data
 * @property string $ferryman_origin
 * @property string $comment
 * @property integer $region_id
 * @property integer $city_id
 * @property integer $ferryman_id
 *
 * @property string $ferrymanName
 *
 * @property Ferrymen $ferryman
 * @property City $city
 * @property Regions $region
 */
class Projects extends \yii\db\ActiveRecord
{
    /**
     * @var string представление перевозчика (либо из поля ferryman_origin либо из поля ferrymanName)
     */
    public $ferrymanRep;

    /**
     * @var string все города, куда ездил перевозчик, одной строкой
     */
    public $cityNames;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'projects';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at'], 'required'],
            [['created_at', 'region_id', 'city_id', 'ferryman_id'], 'integer'],
            [['address', 'data', 'comment'], 'string'],
            [['ferryman_origin'], 'string', 'max' => 100],
            [['ferryman_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ferrymen::className(), 'targetAttribute' => ['ferryman_id' => 'id']],
            [['city_id'], 'exist', 'skipOnError' => true, 'targetClass' => City::className(), 'targetAttribute' => ['city_id' => 'city_id']],
            [['region_id'], 'exist', 'skipOnError' => true, 'targetClass' => Regions::className(), 'targetAttribute' => ['region_id' => 'region_id']],
            [['address', 'data', 'ferryman_origin', 'comment'], 'default', 'value' => null],
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
            'address' => 'Адрес',
            'data' => 'Данные проекта',
            'ferryman_origin' => 'Перевозчик',
            'comment' => 'Примечания',
            'region_id' => 'Регион',
            'city_id' => 'Город',
            'ferryman_id' => 'Перевозчик',
            // вычисляемые поля
            'ferrymanName' => 'Перевозчик',
            'ferrymanRep' => 'Перевозчик',
            'cityName' => 'Город',
            'cityNames' => 'Города',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFerryman()
    {
        return $this->hasOne(Ferrymen::className(), ['id' => 'ferryman_id']);
    }

    /**
     * Возвращает наименование перевозчика.
     * @return string
     */
    public function getFerrymanName()
    {
        return $this->ferryman != null ? $this->ferryman->name_short : $this->ferryman;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::className(), ['city_id' => 'city_id']);
    }

    /**
     * Возвращает наименование населенного пункта.
     * @return string
     */
    public function getCityName()
    {
        return $this->city != null ? $this->city->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegion()
    {
        return $this->hasOne(Regions::className(), ['region_id' => 'region_id']);
    }
}
