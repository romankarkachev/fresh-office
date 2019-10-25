<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "contract_types".
 *
 * @property integer $id
 * @property string $name
 *
 * @property Edf[] $edfs
 */
class ContractTypes extends \yii\db\ActiveRecord
{
    const CONTRACT_TYPE_ПРЕДОПЛАТА = 1;
    const CONTRACT_TYPE_ПОСТОПЛАТА = 2;
    const CONTRACT_TYPE_ОПЛАТА_ЧАСТЯМИ = 3;
    const CONTRACT_TYPE_РАЗОВЫЙ = 4;
    const CONTRACT_TYPE_ПРЕДОПЛАТА_БТ = 5; // без транспорта
    const CONTRACT_TYPE_ПОСТОПЛАТА_БТ = 6;
    const CONTRACT_TYPE_НА_ОДНУ_СДЕЛКУ = 7;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contract_types';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 50],
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
     * Делает выборку типов договоров и возвращает в виде массива.
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
    public function getEdfs()
    {
        return $this->hasMany(Edf::className(), ['ct_id' => 'id']);
    }
}
