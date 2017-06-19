<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "payment_conditions".
 *
 * @property integer $id
 * @property string $name
 */
class PaymentConditions extends \yii\db\ActiveRecord
{
    const PAYMENT_CONDITIONS_ПРЕДОПЛАТА = 1;
    const PAYMENT_CONDITIONS_ПОСТОПЛАТА = 2;
    const PAYMENT_CONDITIONS_ЧАСТЯМИ = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment_conditions';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
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
        ];
    }

    /**
     * Делает выборку условий оплат и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }
}
