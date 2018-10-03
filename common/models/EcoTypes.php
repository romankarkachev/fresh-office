<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "eco_types".
 *
 * @property integer $id
 * @property string $name
 *
 * @property EcoTypesMilestones[] $ecoTypesMilestones
 */
class EcoTypes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'eco_types';
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
     * Выполняет проверку, используется ли запись в других элементах.
     * @return bool
     */
    public function checkIfUsed()
    {
        if ($this->getEcoProjects()->count() > 0 || $this->getEcoTypesMilestones()->count() > 0) return true;

        return false;
    }

    /**
     * Делает выборку типов проектов по экологии и возвращает в виде массива.
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
    public function getEcoProjects()
    {
        return $this->hasMany(EcoProjects::className(), ['type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEcoTypesMilestones()
    {
        return $this->hasMany(EcoTypesMilestones::className(), ['type_id' => 'id']);
    }
}
