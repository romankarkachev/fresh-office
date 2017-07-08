<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "transport_requests_states".
 *
 * @property integer $id
 * @property string $name
 */
class TransportRequestsStates extends \yii\db\ActiveRecord
{
    /**
     * Статусы запросов
     */
    const STATE_НОВЫЙ = 1;
    const STATE_ОБРАБАТЫВАЕТСЯ = 2;
    const STATE_ЗАКРЫТ = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'transport_requests_states';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
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
     * Делает выборку статусов запросов транспорта и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }
}
