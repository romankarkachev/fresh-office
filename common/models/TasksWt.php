<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tasks_wt".
 *
 * @property int $id
 * @property int $task_id Задача
 * @property int $start_at Начало
 * @property int $finish_at Завершение
 *
 * @property Tasks $task
 */
class TasksWt extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tasks_wt';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['task_id'], 'required'],
            [['task_id', 'start_at', 'finish_at'], 'integer'],
            [['task_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tasks::className(), 'targetAttribute' => ['task_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'task_id' => 'Задача',
            'start_at' => 'Начало',
            'finish_at' => 'Завершение',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTask()
    {
        return $this->hasOne(Tasks::className(), ['id' => 'task_id']);
    }
}
