<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "licenses_requests_states".
 *
 * @property integer $id
 * @property string $name
 *
 * @property LicensesRequests[] $licensesRequests
 */
class LicensesRequestsStates extends \yii\db\ActiveRecord
{
    const LICENSE_STATE_НОВЫЙ = 1;
    const LICENSE_STATE_ОДОБРЕН = 2;
    const LICENSE_STATE_ОТКАЗ = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'licenses_requests_states';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 255],
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
     * Делает выборку статусов запросов лицензий и возвращает в виде массива.
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
    public function getLicensesRequests()
    {
        return $this->hasMany(LicensesRequests::className(), ['state_id' => 'id']);
    }
}
