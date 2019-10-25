<?php

namespace common\models;

use Yii;

/**
 * Задачи. Модель, дублирующая foCompanyTasks, но со своими отличиями.
 *
 * @property integer $ID_CONTACT
 * @property integer $ID_COMPANY
 * @property integer $ID_VID_CONTACT вид задачи (встреча | напоминание id 3 | обращение | ...) таблица [VID_CONTACT]
 * @property string $REZULTAT_CONTACT
 * @property string $DATA_CONTACT
 * @property string $DATA_NEXT_CONTACT
 * @property integer $ID_VID_CONTACT_NEXT
 * @property integer $ID_PRIZNAK_CONTACT статус задачи(запланирован id 1 | выполнена id 2 | в процессе id 3) таблица [PRIZNAK_CONTACT]
 * @property string $TIME_CONTACT
 * @property string $TIME_NEXT_CONTACT
 * @property string $PRIMECHANIE
 * @property integer $ID_CONTACT_MAN
 * @property string $DATA_END_CONTACT
 * @property string $ID_CH
 * @property integer $ID_MANAGER
 * @property integer $ID_LIST_STATUS_CONTACT приоритет задачи (стандартная | высокая), таблица [LIST_STATUS_CONTACT]
 * @property integer $TRASH
 * @property string $DATA_CONTACT_FINAL
 * @property string $TIME_CONTACT_FINAL
 * @property integer $ID_MANAGER_EXE
 * @property integer $ID_LIST_PROJECT_COMPANY
 * @property integer $OPTION_SCHLURER
 * @property integer $TYPE_SCHLURER
 * @property string $ACTUAL_DATE_START
 * @property string $ACTUAL_DATE_FINISH
 * @property string $MANAGER_TRASH
 * @property string $DATE_TRASH
 * @property string $SEX
 * @property integer $MARKER_ON
 * @property integer $ID_MANAGER_MARKER
 * @property string $MARKER_DESCRIPTION
 * @property string $DATE_CREATED
 * @property string $DATE_EXECUTION
 * @property integer $N_PP
 * @property integer $ID_DEAL
 * @property integer $ID_LIST_STEP_PROGECT_COMPANY
 * @property integer $IS_HOT
 * @property integer $MIN_TO_NOTIFY
 * @property integer $IS_NOTIFY_MAIL_SENDED
 * @property integer $EMAIL_ID
 *
 * @property string $companyName
 * @property string $managerName
 * @property string $contactPersonName
 * @property string $responsibleProfileName
 *
 * @property foManagers $manager
 * @property foManagers $responsible
 * @property foCompanyContactPersons $contactPerson
 * @property CalEntries[] $calEntries
 * @property foCompany $company
 * @property PRIZNAKCONTACT $iDPRIZNAKCONTACT
 * @property VIDCONTACT $iDVIDCONTACT
 */
class foTasks extends \yii\db\ActiveRecord
{
    public $id;
    public $type_id;
    public $state_id;
    public $created_at;
    public $managerName;
    public $typeName;
    public $stateName;
    public $priorityName;
    public $start_at;
    public $finish_at;
    public $purpose;
    public $solution;
    public $responsibleProfileName;
    public $postponedCount;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'CBaseCRM_Fresh_7x.dbo.LIST_CONTACT_COMPANY';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_mssql');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ID_COMPANY'], 'required'],
            [['ID_COMPANY', 'ID_VID_CONTACT', 'ID_VID_CONTACT_NEXT', 'ID_PRIZNAK_CONTACT', 'ID_CONTACT_MAN', 'ID_MANAGER', 'ID_LIST_STATUS_CONTACT', 'TRASH', 'ID_MANAGER_EXE', 'ID_LIST_PROJECT_COMPANY', 'OPTION_SCHLURER', 'TYPE_SCHLURER', 'MARKER_ON', 'ID_MANAGER_MARKER', 'N_PP', 'ID_DEAL', 'ID_LIST_STEP_PROGECT_COMPANY', 'IS_HOT', 'MIN_TO_NOTIFY', 'IS_NOTIFY_MAIL_SENDED', 'EMAIL_ID'], 'integer'],
            [['REZULTAT_CONTACT', 'PRIMECHANIE', 'ID_CH', 'MANAGER_TRASH', 'SEX', 'MARKER_DESCRIPTION'], 'string'],
            [['DATA_CONTACT', 'DATA_NEXT_CONTACT', 'TIME_CONTACT', 'TIME_NEXT_CONTACT', 'DATA_END_CONTACT', 'DATA_CONTACT_FINAL', 'TIME_CONTACT_FINAL', 'ACTUAL_DATE_START', 'ACTUAL_DATE_FINISH', 'DATE_TRASH', 'DATE_CREATED', 'DATE_EXECUTION'], 'safe'],
            [['ID_COMPANY'], 'exist', 'skipOnError' => true, 'targetClass' => foCompany::class, 'targetAttribute' => ['ID_COMPANY' => 'ID_COMPANY']],
            [['ID_PRIZNAK_CONTACT'], 'exist', 'skipOnError' => true, 'targetClass' => foTasksTypes::class, 'targetAttribute' => ['ID_PRIZNAK_CONTACT' => 'ID_PRIZNAK_CONTACT']],
            [['ID_VID_CONTACT'], 'exist', 'skipOnError' => true, 'targetClass' => foTasksStates::class, 'targetAttribute' => ['ID_VID_CONTACT' => 'ID_VID_CONTACT']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Дата и время создания',
            'createdByProfileName' => 'Автор создания',
            'managerName' => 'Манагер',
            'typeName' => 'Тип',
            'stateName' => 'Статус',
            'priorityName' => 'Приоритет',
            'start_at' => 'Начало',
            'finish_at' => 'Завершение',
            'purpose' => 'Цель',
            'solution' => 'Результат',
            'postponedCount' => 'Переносов',
            'responsibleProfileName' => 'Исполнитель',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManager()
    {
        return $this->hasOne(foManagers::class, ['ID_MANAGER' => 'ID_MANAGER']);
    }

    /**
     * Возвращает имя ответственного по контрагенту менеджера.
     * @return string
     */
    public function getManagerName()
    {
        return !empty($this->manager) ? $this->manager->MANAGER_NAME : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResponsible()
    {
        return $this->hasOne(foManagers::class, ['ID_MANAGER' => 'ID_MANAGER_EXE'])->from(['responsible' => foManagers::tableName()]);
    }

    /**
     * Возвращает имя исполнителя по задаче.
     * @return string
     */
    public function getResponsibleProfileName()
    {
        return !empty($this->responsible) ? $this->responsible->MANAGER_NAME : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContactPerson()
    {
        return $this->hasOne(foCompanyContactPersons::className(), ['ID_CONTACT_MAN' => 'ID_CONTACT_MAN']);
    }

    /**
     * Возвращает имя контактного лица от контрагента.
     * @return string
     */
    public function getContactPersonName()
    {
        return $this->contactPerson != null ? $this->contactPerson->CONTACT_MAN_NAME : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCalEntries()
    {
        return $this->hasMany(CalEntries::className(), ['task_id' => 'ID_CONTACT']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(foCompany::className(), ['ID_COMPANY' => 'ID_COMPANY']);
    }

    /**
     * Возвращает наименование контрагента.
     * @return string
     */
    public function getCompanyName()
    {
        return $this->company ? $this->company->COMPANY_NAME : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getState()
    {
        return $this->hasOne(foTasksStates::class, ['ID_PRIZNAK_CONTACT' => 'ID_PRIZNAK_CONTACT']);
    }

    /**
     * Возвращает наименование статуса задачи.
     * @return string
     */
    public function getStateName()
    {
        return !empty($this->state) ? $this->state->DISCRIPTION_PRIZNAK_CONTACT : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(foTasksTypes::class, ['ID_VID_CONTACT' => 'ID_VID_CONTACT']);
    }

    /**
     * Возвращает наименование типа задачи.
     * @return string
     */
    public function getTypeName()
    {
        return !empty($this->type) ? $this->type->DISCRIPTION_VID_CONTCT : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPriority()
    {
        return $this->hasOne(foTasksPriorities::class, ['ID_LIST_STATUS_CONTACT' => 'ID_LIST_STATUS_CONTACT']);
    }

    /**
     * Возвращает приоритет задачи.
     * @return string
     */
    public function getPriorityName()
    {
        return !empty($this->priority) ? $this->priority->STATUS_CONTACT : '';
    }
}
