<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tasks".
 *
 * @property int $id
 * @property int $created_at Дата и время создания
 * @property int $created_by Автор создания
 * @property int $type_id Тип
 * @property int $state_id Статус
 * @property int $priority_id Приоритет
 * @property int $start_at Начало
 * @property int $finish_at Завершение
 * @property int $fo_ca_id Контрагент
 * @property string $fo_ca_name Контрагент
 * @property int $fo_cp_id Контактное лицо
 * @property string $fo_cp_name Контактное лицо
 * @property int $responsible_id Исполнитель
 * @property int $project_id Проект
 * @property string $purpose Цель
 * @property string $solution Результат
 *
 * @property string $createdByProfileName
 * @property string $typeName
 * @property string $stateName
 * @property string $priorityName
 * @property string $contactPersonName
 * @property string $responsibleProfileName
 *
 * @property User $createdBy
 * @property Profile $createdByProfile
 * @property AuthAssignment $createdByRoles
 * @property TasksPriorities $priority
 * @property User $responsible
 * @property foCompanyContactPersons $contactPerson
 * @property Profile $responsibleProfile
 * @property AuthAssignment $responsibleRoles
 * @property TasksStates $state
 * @property TasksTypes $type
 * @property TasksFiles[] $tasksFiles
 * @property TasksWt[] $tasksWts
 */
class Tasks extends \yii\db\ActiveRecord
{
    /**
     * Псевдонимы присоединяемых таблиц
     */
    const JOIN_CREATED_BY_PROFILE_ALIAS = 'createdByProfile';
    const JOIN_CREATED_BY_ROLES_ALIAS = 'createdByRoles';
    const JOIN_RESPONSIBLE_PROFILE_ALIAS = 'responsibleProfile';
    const JOIN_RESPONSIBLE_ROLES_ALIAS = 'responsibleRoles';

    /**
     * @var integer количество переносов
     */
    public $postponedCount;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tasks';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type_id', 'state_id', 'priority_id'], 'required'],
            [['created_at', 'created_by', 'type_id', 'state_id', 'priority_id', 'start_at', 'finish_at', 'fo_ca_id', 'fo_cp_id', 'responsible_id', 'project_id'], 'integer'],
            [['purpose', 'solution'], 'string'],
            [['fo_ca_name'], 'string', 'max' => 255],
            [['fo_cp_name'], 'string', 'max' => 150],
            [['fo_ca_name', 'fo_cp_name', 'purpose', 'solution'], 'default', 'value' => null],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['priority_id'], 'exist', 'skipOnError' => true, 'targetClass' => TasksPriorities::class, 'targetAttribute' => ['priority_id' => 'id']],
            [['responsible_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['responsible_id' => 'id']],
            [['state_id'], 'exist', 'skipOnError' => true, 'targetClass' => TasksStates::class, 'targetAttribute' => ['state_id' => 'id']],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => TasksTypes::class, 'targetAttribute' => ['type_id' => 'id']],
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
            'created_by' => 'Автор создания',
            'type_id' => 'Тип',
            'state_id' => 'Статус',
            'priority_id' => 'Приоритет',
            'start_at' => 'Начало',
            'finish_at' => 'Завершение',
            'fo_ca_id' => 'Контрагент',
            'fo_ca_name' => 'Контрагент',
            'fo_cp_id' => 'Контактное лицо',
            'fo_cp_name' => 'Контактное лицо',
            'responsible_id' => 'Исполнитель',
            'project_id' => 'Проект',
            'purpose' => 'Цель',
            'solution' => 'Результат',
            // виртуальные поля
            'postponedCount' => 'Переносов',
            // вычисляемые поля
            'createdByProfileName' => 'Автор создания',
            'typeName' => 'Тип',
            'stateName' => 'Статус',
            'priorityName' => 'Приоритет',
            'responsibleProfileName' => 'Исполнитель',
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
                'preserveNonEmptyValues' => true,
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
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            // Удаление связанных объектов перед удалением объекта

            // удаляем возможные файлы
            // deleteAll не вызывает beforeDelete, поэтому делаем перебор
            $files = TasksFiles::find()->where(['task_id' => $this->id])->all();
            foreach ($files as $file) $file->delete();

            return true;
        }

        return false;
    }

    /**
     * Делает выборку контактных лиц текущего контрагента и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public function arrayMapOfContactPersonsForSelect2()
    {
        $contactPersons = DirectMSSQLQueries::fetchCounteragentsContactPersons($this->fo_ca_id);
        if (count($contactPersons) > 0) return ArrayHelper::map($contactPersons, 'id', 'text');

        return [];
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
        return $this->hasOne(Profile::class, ['user_id' => 'created_by'])->from([self::JOIN_CREATED_BY_PROFILE_ALIAS => Profile::tableName()]);
    }

    /**
     * Возвращает имя создателя записи.
     * @return string
     */
    public function getCreatedByProfileName()
    {
        return $this->createdByProfile != null ? ($this->createdByProfile->name != null ? $this->createdByProfile->name : $this->createdBy->username) : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedByRoles()
    {
        return $this->hasOne(AuthAssignment::class, ['user_id' => 'created_by'])->from([self::JOIN_CREATED_BY_ROLES_ALIAS => AuthAssignment::tableName()]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(TasksTypes::class, ['id' => 'type_id']);
    }

    /**
     * Возвращает наименование тип задачи.
     * @return string
     */
    public function getTypeName()
    {
        return !empty($this->type) ? $this->type->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getState()
    {
        return $this->hasOne(TasksStates::class, ['id' => 'state_id']);
    }

    /**
     * Возвращает наименование текущего статуса задачи.
     * @return string
     */
    public function getStateName()
    {
        return !empty($this->state) ? $this->state->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPriority()
    {
        return $this->hasOne(TasksPriorities::class, ['id' => 'priority_id']);
    }

    /**
     * Возвращает наименование приоритета.
     * @return string
     */
    public function getPriorityName()
    {
        return !empty($this->priority) ? $this->priority->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContactPerson()
    {
        return $this->hasOne(foCompanyContactPersons::class, ['ID_CONTACT_MAN' => 'fo_cp_id']);
    }

    /**
     * Возвращает имя контактного лица контрагента.
     * @return string
     */
    public function getContactPersonName()
    {
        return !empty($this->contactPerson) ? $this->contactPerson->CONTACT_MAN_NAME : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResponsible()
    {
        return $this->hasOne(User::class, ['id' => 'responsible_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResponsibleProfile()
    {
        return $this->hasOne(Profile::class, ['user_id' => 'responsible_id'])->from([self::JOIN_RESPONSIBLE_PROFILE_ALIAS => Profile::tableName()]);
    }

    /**
     * Возвращает имя исполнителя по задаче.
     * @return string
     */
    public function getResponsibleProfileName()
    {
        return !empty($this->responsibleProfile) ? (!empty($this->responsibleProfile->name) ? $this->responsibleProfile->name : $this->responsible->username) : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResponsibleRoles()
    {
        return $this->hasOne(AuthAssignment::class, ['user_id' => 'responsible_id'])->from([self::JOIN_RESPONSIBLE_ROLES_ALIAS => AuthAssignment::tableName()]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTasksFiles()
    {
        return $this->hasMany(TasksFiles::class, ['task_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTasksWts()
    {
        return $this->hasMany(TasksWt::class, ['task_id' => 'id']);
    }
}
