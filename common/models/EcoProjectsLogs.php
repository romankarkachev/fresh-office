<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "eco_projects_logs".
 *
 * @property int $id
 * @property int $created_at Дата и время создания
 * @property int $created_by Инициатор
 * @property int $project_id Проект
 * @property int $state_id Статус
 * @property string $description Описание события
 *
 * @property string $createdByProfileName
 * @property string $stateName
 *
 * @property StatesEcoProjects $state
 * @property User $createdBy
 * @property EcoProjects $project
 */
class EcoProjectsLogs extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'eco_projects_logs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project_id'], 'required'],
            [['created_at', 'created_by', 'project_id', 'state_id'], 'integer'],
            [['description'], 'string'],
            [['state_id'], 'exist', 'skipOnError' => true, 'targetClass' => StatesEcoProjects::class, 'targetAttribute' => ['state_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => EcoProjects::class, 'targetAttribute' => ['project_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Дата и время создания',
            'created_by' => 'Инициатор',
            'project_id' => 'Проект',
            'state_id' => 'Статус',
            'description' => 'Описание события',
            // вычисляемые поля
            'createdByProfileName' => 'Автор',
            'stateName' => 'Статус',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
            'blameable' => [
                'class' => 'yii\behaviors\BlameableBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_by'],
                ],
                'preserveNonEmptyValues' => true,
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedByProfile()
    {
        return $this->hasOne(Profile::class, ['user_id' => 'created_by']);
    }

    /**
     * Возвращает имя создателя записи.
     * @return string
     */
    public function getCreatedByProfileName()
    {
        return !empty($this->createdByProfile) ? (!empty($this->createdByProfile->name) ? $this->createdByProfile->name : $this->createdBy->username) : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getState()
    {
        return $this->hasOne(StatesEcoProjects::class, ['id' => 'state_id']);
    }

    /**
     * Возвращает наименование статуса.
     * @return string
     */
    public function getStateName()
    {
        return $this->state ? $this->state->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(EcoProjects::class, ['id' => 'project_id']);
    }
}
