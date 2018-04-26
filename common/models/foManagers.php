<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "MANAGERS".
 *
 * @property integer $ID_MANAGER
 * @property string $MANAGER_NAME
 * @property string $DATA_INPUT
 * @property string $DATA_HAPY
 * @property string $TELEPHON_HOME
 * @property string $TELEPHON_JOB
 * @property string $ADRES_HOME
 * @property string $PASWORD
 * @property string $LOGIN
 * @property integer $TIP
 * @property string $e_mail
 * @property integer $id_group
 * @property integer $ONLINE
 * @property string $REGONLINE
 * @property string $EXCEL_SAVE_LIST_COMPANY
 * @property string $EXCEL_SAVE_REPORT
 * @property string $SAVE_REPORT_COMPANY
 * @property integer $VER
 * @property integer $ID_LIST_SIP_CHANEL
 * @property string $SIP_LOGIN
 * @property string $SIP_PASW
 * @property string $SIP_USER
 * @property integer $ID_GROUP_COMPANY
 * @property string $ENABLED
 * @property string $SHOW_PHONE_PANEL
 * @property string $SAVE_ALL_CALL
 * @property integer $COLOR_SCHLURER_MARKER
 * @property string $TIME_ZONE_OFFSET
 * @property string $TIME_SUMMER
 * @property integer $ID_SIP_ACCOUNT
 * @property integer $ID_COMPANY
 * @property integer $HIDDEN_MODULES
 * @property integer $COMPANY_DATA_ID
 * @property integer $ID_KPI
 * @property integer $DOUBLE_LOGIN
 * @property string $AVATAR_URL
 * @property integer $EMAIL_NOTIFICATION
 * @property string $AVATAR_URL_KPI
 * @property string $COVER_URL
 * @property string $GOOGLEDISC_LOGIN
 * @property string $GOOGLEDISC_PASS
 * @property string $DROPBOX_LOGIN
 * @property string $DROPBOX_PASS
 * @property integer $ID_CONTACT_MAN
 * @property integer $IS_NOT_FIRST_LOGIN
 * @property string $latest_version
 * @property integer $WEBINAR_NOTIFICATION
 * @property string $VOXIMPLANT_USERNAME
 * @property string $MANAGER_POSITION
 * @property integer $has_iron_phone
 * @property integer $IS_OUTER_CHAT_ON
 *
 * @property CalEntryTypes[] $calEntryTypes
 * @property VIDCONTACT[] $vids
 * @property Calendars[] $calendars
 * @property COMPANY[] $cOMPANies
 * @property LISTEMAILACCOUNT[] $lISTEMAILACCOUNTs
 * @property GROUPS $idGroup
 * @property LISTSPRKPI $iDKPI
 * @property PHONECALLLOG[] $pHONECALLLOGs
 * @property PHONELOG[] $pHONELOGs
 * @property PHONELOG[] $pHONELOGs0
 * @property PHONEUSERLOG[] $pHONEUSERLOGs
 * @property PHONEUSERLOG[] $pHONEUSERLOGs0
 * @property VoxManagerPriority[] $voxManagerPriorities
 * @property VOXQUEUES[] $acdQueues
 */
class foManagers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'MANAGERS';
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
            [['MANAGER_NAME', 'PASWORD', 'LOGIN', 'TIP', 'id_group'], 'required'],
            [['MANAGER_NAME', 'TELEPHON_HOME', 'TELEPHON_JOB', 'ADRES_HOME', 'PASWORD', 'LOGIN', 'e_mail', 'EXCEL_SAVE_LIST_COMPANY', 'EXCEL_SAVE_REPORT', 'SAVE_REPORT_COMPANY', 'SIP_LOGIN', 'SIP_PASW', 'SIP_USER', 'ENABLED', 'SHOW_PHONE_PANEL', 'SAVE_ALL_CALL', 'TIME_ZONE_OFFSET', 'TIME_SUMMER', 'AVATAR_URL', 'AVATAR_URL_KPI', 'COVER_URL', 'GOOGLEDISC_LOGIN', 'GOOGLEDISC_PASS', 'DROPBOX_LOGIN', 'DROPBOX_PASS', 'latest_version', 'VOXIMPLANT_USERNAME', 'MANAGER_POSITION'], 'string'],
            [['DATA_INPUT', 'DATA_HAPY', 'REGONLINE'], 'safe'],
            [['TIP', 'id_group', 'ONLINE', 'VER', 'ID_LIST_SIP_CHANEL', 'ID_GROUP_COMPANY', 'COLOR_SCHLURER_MARKER', 'ID_SIP_ACCOUNT', 'ID_COMPANY', 'HIDDEN_MODULES', 'COMPANY_DATA_ID', 'ID_KPI', 'DOUBLE_LOGIN', 'EMAIL_NOTIFICATION', 'ID_CONTACT_MAN', 'IS_NOT_FIRST_LOGIN', 'WEBINAR_NOTIFICATION', 'has_iron_phone', 'IS_OUTER_CHAT_ON'], 'integer'],
            [['id_group'], 'exist', 'skipOnError' => true, 'targetClass' => GROUPS::className(), 'targetAttribute' => ['id_group' => 'ID_GROUP']],
            [['ID_KPI'], 'exist', 'skipOnError' => true, 'targetClass' => LISTSPRKPI::className(), 'targetAttribute' => ['ID_KPI' => 'ID_KPI']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID_MANAGER' => 'Id  Manager',
            'MANAGER_NAME' => 'Manager  Name',
            'DATA_INPUT' => 'Data  Input',
            'DATA_HAPY' => 'Data  Hapy',
            'TELEPHON_HOME' => 'Telephon  Home',
            'TELEPHON_JOB' => 'Telephon  Job',
            'ADRES_HOME' => 'Adres  Home',
            'PASWORD' => 'Pasword',
            'LOGIN' => 'Login',
            'TIP' => 'Tip',
            'e_mail' => 'E Mail',
            'id_group' => 'Id Group',
            'ONLINE' => 'Online',
            'REGONLINE' => 'Regonline',
            'EXCEL_SAVE_LIST_COMPANY' => 'Excel  Save  List  Company',
            'EXCEL_SAVE_REPORT' => 'Excel  Save  Report',
            'SAVE_REPORT_COMPANY' => 'Save  Report  Company',
            'VER' => 'Ver',
            'ID_LIST_SIP_CHANEL' => 'Id  List  Sip  Chanel',
            'SIP_LOGIN' => 'Sip  Login',
            'SIP_PASW' => 'Sip  Pasw',
            'SIP_USER' => 'Sip  User',
            'ID_GROUP_COMPANY' => 'Id  Group  Company',
            'ENABLED' => 'Enabled',
            'SHOW_PHONE_PANEL' => 'Show  Phone  Panel',
            'SAVE_ALL_CALL' => 'Save  All  Call',
            'COLOR_SCHLURER_MARKER' => 'Color  Schlurer  Marker',
            'TIME_ZONE_OFFSET' => 'Time  Zone  Offset',
            'TIME_SUMMER' => 'Time  Summer',
            'ID_SIP_ACCOUNT' => 'Id  Sip  Account',
            'ID_COMPANY' => 'Id  Company',
            'HIDDEN_MODULES' => 'Список скрытых для пользовател',
            'COMPANY_DATA_ID' => 'Последний использованный фильт',
            'ID_KPI' => 'Id  Kpi',
            'DOUBLE_LOGIN' => 'Double  Login',
            'AVATAR_URL' => 'Avatar  Url',
            'EMAIL_NOTIFICATION' => 'Email  Notification',
            'AVATAR_URL_KPI' => 'Avatar  Url  Kpi',
            'COVER_URL' => 'Cover  Url',
            'GOOGLEDISC_LOGIN' => 'Googledisc  Login',
            'GOOGLEDISC_PASS' => 'Googledisc  Pass',
            'DROPBOX_LOGIN' => 'Dropbox  Login',
            'DROPBOX_PASS' => 'Dropbox  Pass',
            'ID_CONTACT_MAN' => 'Id  Contact  Man',
            'IS_NOT_FIRST_LOGIN' => 'Is  Not  First  Login',
            'latest_version' => 'Latest Version',
            'WEBINAR_NOTIFICATION' => 'Webinar  Notification',
            'VOXIMPLANT_USERNAME' => 'Voximplant  Username',
            'MANAGER_POSITION' => 'Manager  Position',
            'has_iron_phone' => 'Has Iron Phone',
            'IS_OUTER_CHAT_ON' => 'Is  Outer  Chat  On',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCalEntryTypes()
    {
        return $this->hasMany(CalEntryTypes::className(), ['manager_id' => 'ID_MANAGER']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVids()
    {
        return $this->hasMany(VIDCONTACT::className(), ['ID_VID_CONTACT' => 'vid_id'])->viaTable('cal_entry_types', ['manager_id' => 'ID_MANAGER']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCalendars()
    {
        return $this->hasMany(Calendars::className(), ['manager_id' => 'ID_MANAGER']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCOMPANies()
    {
        return $this->hasMany(COMPANY::className(), ['ID_MANAGER' => 'ID_MANAGER']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLISTEMAILACCOUNTs()
    {
        return $this->hasMany(LISTEMAILACCOUNT::className(), ['ID_MANAGER' => 'ID_MANAGER']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdGroup()
    {
        return $this->hasOne(GROUPS::className(), ['ID_GROUP' => 'id_group']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIDKPI()
    {
        return $this->hasOne(LISTSPRKPI::className(), ['ID_KPI' => 'ID_KPI']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPHONECALLLOGs()
    {
        return $this->hasMany(PHONECALLLOG::className(), ['MAN_ID' => 'ID_MANAGER']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPHONELOGs()
    {
        return $this->hasMany(PHONELOG::className(), ['MAN_ID' => 'ID_MANAGER']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPHONELOGs0()
    {
        return $this->hasMany(PHONELOG::className(), ['MANAGER_ID' => 'ID_MANAGER']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPHONEUSERLOGs()
    {
        return $this->hasMany(PHONEUSERLOG::className(), ['MAN_ID' => 'ID_MANAGER']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPHONEUSERLOGs0()
    {
        return $this->hasMany(PHONEUSERLOG::className(), ['MANAGER_ID' => 'ID_MANAGER']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVoxManagerPriorities()
    {
        return $this->hasMany(VoxManagerPriority::className(), ['id_manager' => 'ID_MANAGER']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAcdQueues()
    {
        return $this->hasMany(VOXQUEUES::className(), ['ACD_QUEUE_ID' => 'acd_queue_id'])->viaTable('vox_manager_priority', ['id_manager' => 'ID_MANAGER']);
    }
}
