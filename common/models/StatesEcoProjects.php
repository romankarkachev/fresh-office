<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "states_eco_projects".
 *
 * @property int $id
 * @property string $name Наименование
 *
 * @property EcoProjects[] $ecoProjects
 * @property EcoProjectsLogs[] $ecoProjectsLogs
 */
class StatesEcoProjects extends \yii\db\ActiveRecord
{
    /**
     * Предопределенные значения
     */
    const STATE_ОЖИДАНИЕ_ИСПОЛНИТЕЛЯ = 2;
    const STATE_ОЖИДАНИЕ_ЗАКАЗЧИКА = 3;
    const STATE_НАДЗОРНЫЙ_ОРГАН = 4;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'states_eco_projects';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
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
        if (!empty($this->getEcoProjects()) || !empty($this->getEcoProjectsLogs())) return true;

        return false;
    }

    /**
     * Делает выборку статустов и возвращает в виде массива.
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
        return $this->hasMany(EcoProjects::class, ['state_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEcoProjectsLogs()
    {
        return $this->hasMany(EcoProjectsLogs::class, ['state_id' => 'id']);
    }
}
