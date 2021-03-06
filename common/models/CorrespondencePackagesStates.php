<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "correspondence_packages_states".
 *
 * @property integer $id
 * @property string $name
 *
 * @property CorrespondencePackages[] $correspondencePackages
 */
class CorrespondencePackagesStates extends \yii\db\ActiveRecord
{
    const STATE_ЧЕРНОВИК = 1;
    const STATE_СОГЛАСОВАНИЕ = 2;
    const STATE_УТВЕРЖДЕН = 3;
    const STATE_ОТКАЗ = 4;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'correspondence_packages_states';
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
     * Делает выборку стт и возвращает в виде массива.
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
    public function getCorrespondencePackages()
    {
        return $this->hasMany(CorrespondencePackages::className(), ['cps_id' => 'id']);
    }
}
