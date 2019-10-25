<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "units".
 *
 * @property int $id
 * @property string $name Наименование
 * @property string $name_full Наименование полное
 * @property string $code Международный код
 *
 * @property TransportRequestsWaste[] $transportRequestsWaste
 * @property EdfTp[] $edfTableParts
 */
class Units extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'units';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 30],
            [['name_full'], 'string', 'max' => 100],
            [['code'], 'string', 'max' => 3],
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
            'name_full' => 'Наименование полное',
            'code' => 'Международный код',
        ];
    }

    /**
     * Выполняет проверку, используется ли запись в других элементах.
     * @return bool
     */
    public function checkIfUsed()
    {
        if ($this->getTransportRequestsWaste()->count() > 0) return true;

        return false;
    }

    /**
     * Делает выборку единиц измерения и возвращает в виде массива.
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
    public function getTransportRequestsWaste()
    {
        return $this->hasMany(TransportRequestsWaste::className(), ['unit_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEdfTableParts()
    {
        return $this->hasMany(EdfTp::className(), ['unit_id' => 'id']);
    }
}
