<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "eco_milestones".
 *
 * @property integer $id
 * @property string $name
 *
 * @property EcoTypesMilestones[] $ecoTypesMilestones
 * @property EcoProjectsMilestones[] $ecoProjectsMilestones
 */
class EcoMilestones extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'eco_milestones';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
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
     * Выполняет проверку, используется ли запись в других элементах.
     * @return bool
     */
    public function checkIfUsed()
    {
        if ($this->getEcoTypesMilestones()->count() > 0 || $this->getEcoProjectsMilestones()->count() > 0) return true;

        return false;
    }

    /**
     * Делает выборку этапов проектов и возвращает в виде массива.
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
    public function getEcoTypesMilestones()
    {
        return $this->hasMany(EcoTypesMilestones::className(), ['milestone_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEcoProjectsMilestones()
    {
        return $this->hasMany(EcoProjectsMilestones::className(), ['milestone_id' => 'id']);
    }
}
