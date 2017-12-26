<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "payment_orders_states".
 *
 * @property integer $id
 * @property string $name
 *
 * @property PaymentOrders[] $paymentOrders
 */
class PaymentOrdersStates extends \yii\db\ActiveRecord
{
    const PAYMENT_STATE_ЧЕРНОВИК = 1;
    const PAYMENT_STATE_СОГЛАСОВАНИЕ = 2;
    const PAYMENT_STATE_УТВЕРЖДЕН = 3;
    const PAYMENT_STATE_ОПЛАЧЕН = 4;
    const PAYMENT_STATE_ОТКАЗ = 5;

    /**
     * Набор статусов по-умолчанию для бухгалтера.
     */
    const PAYMENT_STATES_SET_ACCOUNTANT_DEFAULT = [
        self::PAYMENT_STATE_УТВЕРЖДЕН,
    ];

    /**
     * Набор статусов оплаченных ордеров для бухгалтера.
     */
    const PAYMENT_STATES_SET_ACCOUNTANT_PAID = [
        self::PAYMENT_STATE_ОПЛАЧЕН,
    ];

    /**
     * Набор всех доступных для бухгалтера статусов.
     */
    const PAYMENT_STATES_SET_ACCOUNTANT_ALL = [
        self::PAYMENT_STATE_УТВЕРЖДЕН,
        self::PAYMENT_STATE_ОПЛАЧЕН,
    ];

    /**
     * Набор статусов для управления кнопками.
     */
    const PAYMENT_STATES_SET_RECORD_CONFIRMED = [
        PaymentOrdersStates::PAYMENT_STATE_СОГЛАСОВАНИЕ,
        PaymentOrdersStates::PAYMENT_STATE_УТВЕРЖДЕН,
        PaymentOrdersStates::PAYMENT_STATE_ОТКАЗ,
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment_orders_states';
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
     * Делает выборку статусов и возвращает в виде массива.
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
    public function getPaymentOrders()
    {
        return $this->hasMany(PaymentOrders::className(), ['state_id' => 'id']);
    }
}
