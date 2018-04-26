<?php

namespace common\models;

use Yii;

/**
 * Задачи по контрагенту.
 *
 * @property integer $ID_CONTACT
 * @property integer $ID_COMPANY
 * @property integer $ID_VID_CONTACT
 * @property string $REZULTAT_CONTACT
 * @property string $DATA_CONTACT
 * @property string $DATA_NEXT_CONTACT
 * @property integer $ID_VID_CONTACT_NEXT
 * @property integer $ID_PRIZNAK_CONTACT
 * @property string $TIME_CONTACT
 * @property string $TIME_NEXT_CONTACT
 * @property string $PRIMECHANIE
 * @property integer $ID_CONTACT_MAN
 * @property string $DATA_END_CONTACT
 * @property string $ID_CH
 * @property integer $ID_MANAGER
 * @property integer $ID_LIST_STATUS_CONTACT
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
 * @property string $managerName
 * @property string $contactPersonName
 *
 * @property foManagers $manager
 * @property foCompanyContactPersons $contactPerson
 * @property CalEntries[] $calEntries
 * @property COMPANY $iDCOMPANY
 * @property PRIZNAKCONTACT $iDPRIZNAKCONTACT
 * @property VIDCONTACT $iDVIDCONTACT
 */
class foCompanyTasks extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'LIST_CONTACT_COMPANY';
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
            [['ID_COMPANY'], 'exist', 'skipOnError' => true, 'targetClass' => foCompany::className(), 'targetAttribute' => ['ID_COMPANY' => 'ID_COMPANY']],
            [['ID_PRIZNAK_CONTACT'], 'exist', 'skipOnError' => true, 'targetClass' => PRIZNAKCONTACT::className(), 'targetAttribute' => ['ID_PRIZNAK_CONTACT' => 'ID_PRIZNAK_CONTACT']],
            [['ID_VID_CONTACT'], 'exist', 'skipOnError' => true, 'targetClass' => VIDCONTACT::className(), 'targetAttribute' => ['ID_VID_CONTACT' => 'ID_VID_CONTACT']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID_CONTACT' => 'Id  Contact',
            'ID_COMPANY' => 'Id  Company',
            'ID_VID_CONTACT' => 'Id  Vid  Contact',
            'REZULTAT_CONTACT' => 'Rezultat  Contact',
            'DATA_CONTACT' => 'Data  Contact',
            'DATA_NEXT_CONTACT' => 'Data  Next  Contact',
            'ID_VID_CONTACT_NEXT' => 'Id  Vid  Contact  Next',
            'ID_PRIZNAK_CONTACT' => 'Id  Priznak  Contact',
            'TIME_CONTACT' => 'Time  Contact',
            'TIME_NEXT_CONTACT' => 'Time  Next  Contact',
            'PRIMECHANIE' => 'Primechanie',
            'ID_CONTACT_MAN' => 'Id  Contact  Man',
            'DATA_END_CONTACT' => 'Data  End  Contact',
            'ID_CH' => 'Id  Ch',
            'ID_MANAGER' => 'Id  Manager',
            'ID_LIST_STATUS_CONTACT' => 'Id  List  Status  Contact',
            'TRASH' => 'Trash',
            'DATA_CONTACT_FINAL' => 'Data  Contact  Final',
            'TIME_CONTACT_FINAL' => 'Time  Contact  Final',
            'ID_MANAGER_EXE' => 'Id  Manager  Exe',
            'ID_LIST_PROJECT_COMPANY' => 'Id  List  Project  Company',
            'OPTION_SCHLURER' => 'Option  Schlurer',
            'TYPE_SCHLURER' => 'Type  Schlurer',
            'ACTUAL_DATE_START' => 'Actual  Date  Start',
            'ACTUAL_DATE_FINISH' => 'Actual  Date  Finish',
            'MANAGER_TRASH' => 'Manager  Trash',
            'DATE_TRASH' => 'Date  Trash',
            'SEX' => 'Sex',
            'MARKER_ON' => 'Marker  On',
            'ID_MANAGER_MARKER' => 'Id  Manager  Marker',
            'MARKER_DESCRIPTION' => 'Marker  Description',
            'DATE_CREATED' => 'Date  Created',
            'DATE_EXECUTION' => 'Дата и время выполнения задачи',
            'N_PP' => 'N  Pp',
            'ID_DEAL' => 'Id  Deal',
            'ID_LIST_STEP_PROGECT_COMPANY' => 'Id  List  Step  Progect  Company',
            'IS_HOT' => 'Is  Hot',
            'MIN_TO_NOTIFY' => 'Min  To  Notify',
            'IS_NOTIFY_MAIL_SENDED' => 'Is  Notify  Mail  Sended',
            'EMAIL_ID' => 'Email  ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManager()
    {
        return $this->hasOne(foManagers::className(), ['ID_MANAGER' => 'ID_MANAGER']);
    }

    /**
     * Возвращает имя ответственного по контрагенту менеджера.
     * @return string
     */
    public function getManagerName()
    {
        return $this->manager != null ? $this->manager->MANAGER_NAME : '';
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
    public function getIDCOMPANY()
    {
        return $this->hasOne(COMPANY::className(), ['ID_COMPANY' => 'ID_COMPANY']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIDPRIZNAKCONTACT()
    {
        return $this->hasOne(PRIZNAKCONTACT::className(), ['ID_PRIZNAK_CONTACT' => 'ID_PRIZNAK_CONTACT']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIDVIDCONTACT()
    {
        return $this->hasOne(VIDCONTACT::className(), ['ID_VID_CONTACT' => 'ID_VID_CONTACT']);
    }
}
