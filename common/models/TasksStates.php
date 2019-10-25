<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tasks_states".
 *
 * @property int $id
 * @property string $name Наименование
 *
 * @property Tasks[] $tasks
 */
class TasksStates extends \yii\db\ActiveRecord
{
    const STATE_ЗАПЛАНИРОВАНА = 1;
    const STATE_В_ПРОЦЕССЕ = 2;
    const STATE_ВЫПОЛНЕНА = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tasks_states';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 150],
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
        if ($this->getTasks()->count() > 0) return true;

        return false;
    }

    /**
     * Делает выборку статусов задач и возвращает в виде массива.
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
    public function getTasks()
    {
        return $this->hasMany(Tasks::class, ['state_id' => 'id']);
    }
}
