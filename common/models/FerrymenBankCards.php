<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ferrymen_bank_cards".
 *
 * @property integer $id
 * @property integer $ferryman_id
 * @property string $cardholder
 * @property string $number
 * @property string $bank
 *
 * @property Ferrymen $ferryman
 */
class FerrymenBankCards extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ferrymen_bank_cards';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ferryman_id', 'number'], 'required'],
            [['ferryman_id'], 'integer'],
            [['cardholder'], 'string', 'max' => 100],
            [['number'], 'string', 'max' => 20],
            [['bank'], 'string', 'max' => 255],
            [['ferryman_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ferrymen::className(), 'targetAttribute' => ['ferryman_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ferryman_id' => 'Перевозчик',
            'cardholder' => 'Собственник',
            'number' => 'Номер карты',
            'bank' => 'Банк, которому принадлежит карта',
        ];
    }

    /**
     * Делает выборку способов расчетов с перевозчиками и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @param $ferryman_id integer идентификатор перевозчика
     * @return array
     */
    public static function arrayMapForSelect2($ferryman_id)
    {
        if (!isset($ferryman_id)) return [];

        return ArrayHelper::map(self::find()->where(['ferryman_id' => $ferryman_id])->all(), 'id', 'number');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFerryman()
    {
        return $this->hasOne(Ferrymen::className(), ['id' => 'ferryman_id']);
    }
}
