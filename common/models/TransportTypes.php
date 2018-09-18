<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "transport_types".
 *
 * @property integer $id
 * @property string $name
 * @property integer $is_spec
 * @property integer $unloading_time
 *
 * @property Transport[] $transports
 * @property TransportByCitiesCost[] $transportByCitiesCosts
 * @property TransportRequestsTransport[] $transportRequestsTransports
 */
class TransportTypes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'transport_types';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['is_spec', 'unloading_time'], 'integer'],
            [['name'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование',
            'is_spec' => 'Является спецтехникой',
            'unloading_time' => 'Время на разгрузку (мин.)',
        ];
    }

    /**
     * Выполняет проверку, используется ли запись в других элементах.
     * @return bool
     */
    public function checkIfUsed()
    {
        if ($this->getTransports()->count() > 0) return true;

        return false;
    }

    /**
     * Делает выборку типов техники и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransports()
    {
        return $this->hasMany(Transport::className(), ['tt_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransportByCitiesCosts()
    {
        return $this->hasMany(TransportByCitiesCost::className(), ['tt_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransportRequestsTransports()
    {
        return $this->hasMany(TransportRequestsTransport::className(), ['tt_id' => 'id']);
    }
}
