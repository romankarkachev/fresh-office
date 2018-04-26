<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "COMPANY".
 *
 * @property integer $ID_COMPANY
 * @property string $OKPO
 * @property string $INN
 * @property string $COMPANY_NAME
 * @property string $ADRES
 * @property string $CITY
 * @property integer $ID_MANAGER
 * @property integer $ID_OFFICE
 * @property string $DATA_INPUT
 * @property integer $ID_VID_COMPANY
 * @property string $ROD_DEYATEL
 * @property integer $id_group_company
 * @property string $URL_COMPANY
 * @property string $ID_CH
 * @property string $PUBLIC_COMPANY
 * @property string $DOP_INF
 * @property integer $YUR_FIZ
 * @property integer $ID_LIST_STATUS_COMPANY
 * @property string $INFORM_IN_COMPANY
 * @property string $DR_COMPANY
 * @property string $PROF_HOLIDAY
 * @property string $MANAGER_NAME_CREATER_COMPANY
 * @property string $COUNTRY_COMPANY
 * @property string $FORM_SOBST_COMPANY
 * @property integer $TRASH
 * @property string $FAM_FIZ
 * @property string $NAME_FIZ
 * @property string $OTCH_FIZ
 * @property string $FAM_LAT_FIZ
 * @property string $NAME_LAT_FIZ
 * @property string $REGION
 * @property string $MESTO_RABOT_FIZ
 * @property string $DOLGNOST_RABOT_FIZ
 * @property string $ADDRESS_RABOT_FIZ
 * @property string $POL_ANKT
 * @property string $SEM_POLOJ_ANKT
 * @property string $M_RJD_ANKT
 * @property string $COUNTRY_RJD_ANKT
 * @property string $GRAJD_ANKT
 * @property string $PASPORT_LOCAL_NUMBER
 * @property string $PASPORT_LOCAL_SER
 * @property string $PASPORT_LOCAL_DATE
 * @property string $PASPORT_LOCAL_KEM
 * @property string $PASPORT_LOCAL_NUMB_PODRAZDEL
 * @property string $PASPORT_ZAGRAN_TIP
 * @property string $PASPORT_ZAGRAN_NUMBER
 * @property string $PASPORT_ZAGRAN_SER
 * @property string $PASPORT_ZAGRAN_DATE
 * @property string $PASPORT_ZAGRAN_FINAL
 * @property string $PASPORT_ZAGRAN_KEM
 * @property string $MANAGER_TRASH
 * @property string $DATE_TRASH
 * @property string $CODE_1C
 * @property integer $MARKER_ON
 * @property integer $ID_MANAGER_MARKER
 * @property string $MARKER_DESCRIPTION
 * @property integer $ID_CATEGORY
 * @property integer $ID_AGENT
 * @property string $ADD_forma_oplati
 * @property string $ADD_date_finish
 * @property string $ADD_numb_dogovor
 * @property string $ADD_crm_control
 * @property string $ADD_who
 * @property integer $IS_INTERNAL
 * @property string $ADD_KOD1C_eko_korp
 * @property string $ADD_KOD1C_new
 * @property string $ADD_KOD1C_general2
 * @property string $ADD_KOD1C_logistika
 * @property string $ADD_KOD1C_cuop
 * @property string $ADD_dog_orig
 * @property string $ADD_scan_dog
 * @property integer $ADD_days_post
 *
 * @property string $managerName
 * @property string $managerPhone
 * @property string $managerEmail
 *
 * @property foManagers $manager
 * @property array $tasksInProgress выполняемые и запланированные задачи по контрагенту
 * @property double $balanceForCustomer количество баллов у клиента, рассчитанное от оборота с ним
 * @property GROUPSCOMPANY $idGroupCompany
 * @property VIDCOMPANY $iDVIDCOMPANY
 * @property LISTCOMPANYEMAILS[] $lISTCOMPANYEMAILSs
 * @property LISTCONTACTCOMPANY[] $lISTCONTACTCOMPANies
 * @property LISTDOCUMENTS[] $lISTDOCUMENTSs
 * @property LISTGOODSCOMPANY[] $lISTGOODSCOMPANies
 * @property LISTSALETOVAR[] $lISTSALETOVARs
 * @property LISTSTOCKDOC[] $lISTSTOCKDOCs
 * @property LISTSTOCKDOC[] $lISTSTOCKDOCs0
 * @property PHONELOG[] $pHONELOGs
 * @property PHONEUSERLOG[] $pHONEUSERLOGs
 */
class foCompany extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'COMPANY';
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
            [['OKPO', 'INN', 'COMPANY_NAME', 'ADRES', 'CITY', 'ROD_DEYATEL', 'URL_COMPANY', 'ID_CH', 'PUBLIC_COMPANY', 'DOP_INF', 'INFORM_IN_COMPANY', 'MANAGER_NAME_CREATER_COMPANY', 'COUNTRY_COMPANY', 'FORM_SOBST_COMPANY', 'FAM_FIZ', 'NAME_FIZ', 'OTCH_FIZ', 'FAM_LAT_FIZ', 'NAME_LAT_FIZ', 'REGION', 'MESTO_RABOT_FIZ', 'DOLGNOST_RABOT_FIZ', 'ADDRESS_RABOT_FIZ', 'POL_ANKT', 'SEM_POLOJ_ANKT', 'M_RJD_ANKT', 'COUNTRY_RJD_ANKT', 'GRAJD_ANKT', 'PASPORT_LOCAL_NUMBER', 'PASPORT_LOCAL_SER', 'PASPORT_LOCAL_KEM', 'PASPORT_LOCAL_NUMB_PODRAZDEL', 'PASPORT_ZAGRAN_TIP', 'PASPORT_ZAGRAN_NUMBER', 'PASPORT_ZAGRAN_SER', 'PASPORT_ZAGRAN_KEM', 'MANAGER_TRASH', 'CODE_1C', 'MARKER_DESCRIPTION', 'ADD_forma_oplati', 'ADD_numb_dogovor', 'ADD_crm_control', 'ADD_who', 'ADD_KOD1C_eko_korp', 'ADD_KOD1C_new', 'ADD_KOD1C_general2', 'ADD_KOD1C_logistika', 'ADD_KOD1C_cuop'], 'string'],
            [['ID_MANAGER', 'ID_OFFICE', 'ID_VID_COMPANY', 'id_group_company', 'YUR_FIZ', 'ID_LIST_STATUS_COMPANY', 'TRASH', 'MARKER_ON', 'ID_MANAGER_MARKER', 'ID_CATEGORY', 'ID_AGENT', 'IS_INTERNAL', 'ADD_days_post'], 'integer'],
            [['DATA_INPUT', 'DR_COMPANY', 'PROF_HOLIDAY', 'PASPORT_LOCAL_DATE', 'PASPORT_ZAGRAN_DATE', 'PASPORT_ZAGRAN_FINAL', 'DATE_TRASH', 'ADD_date_finish', 'ADD_dog_orig', 'ADD_scan_dog'], 'safe'],
            [['id_group_company'], 'exist', 'skipOnError' => true, 'targetClass' => GROUPSCOMPANY::className(), 'targetAttribute' => ['id_group_company' => 'id_group_company']],
            [['ID_MANAGER'], 'exist', 'skipOnError' => true, 'targetClass' => foManagers::className(), 'targetAttribute' => ['ID_MANAGER' => 'ID_MANAGER']],
            [['ID_VID_COMPANY'], 'exist', 'skipOnError' => true, 'targetClass' => VIDCOMPANY::className(), 'targetAttribute' => ['ID_VID_COMPANY' => 'ID_VID_COMPANY']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID_COMPANY' => 'Id  Company',
            'OKPO' => 'Okpo',
            'INN' => 'Inn',
            'COMPANY_NAME' => 'Company  Name',
            'ADRES' => 'Adres',
            'CITY' => 'City',
            'ID_MANAGER' => 'Id  Manager',
            'ID_OFFICE' => 'Id  Office',
            'DATA_INPUT' => 'Data  Input',
            'ID_VID_COMPANY' => 'Id  Vid  Company',
            'ROD_DEYATEL' => 'Rod  Deyatel',
            'id_group_company' => 'Id Group Company',
            'URL_COMPANY' => 'Url  Company',
            'ID_CH' => 'Id  Ch',
            'PUBLIC_COMPANY' => 'Public  Company',
            'DOP_INF' => 'Dop  Inf',
            'YUR_FIZ' => 'Yur  Fiz',
            'ID_LIST_STATUS_COMPANY' => 'Id  List  Status  Company',
            'INFORM_IN_COMPANY' => 'Inform  In  Company',
            'DR_COMPANY' => 'Dr  Company',
            'PROF_HOLIDAY' => 'Prof  Holiday',
            'MANAGER_NAME_CREATER_COMPANY' => 'Manager  Name  Creater  Company',
            'COUNTRY_COMPANY' => 'Country  Company',
            'FORM_SOBST_COMPANY' => 'Form  Sobst  Company',
            'TRASH' => 'Trash',
            'FAM_FIZ' => 'Fam  Fiz',
            'NAME_FIZ' => 'Name  Fiz',
            'OTCH_FIZ' => 'Otch  Fiz',
            'FAM_LAT_FIZ' => 'Fam  Lat  Fiz',
            'NAME_LAT_FIZ' => 'Name  Lat  Fiz',
            'REGION' => 'Region',
            'MESTO_RABOT_FIZ' => 'Mesto  Rabot  Fiz',
            'DOLGNOST_RABOT_FIZ' => 'Dolgnost  Rabot  Fiz',
            'ADDRESS_RABOT_FIZ' => 'Address  Rabot  Fiz',
            'POL_ANKT' => 'Pol  Ankt',
            'SEM_POLOJ_ANKT' => 'Sem  Poloj  Ankt',
            'M_RJD_ANKT' => 'M  Rjd  Ankt',
            'COUNTRY_RJD_ANKT' => 'Country  Rjd  Ankt',
            'GRAJD_ANKT' => 'Grajd  Ankt',
            'PASPORT_LOCAL_NUMBER' => 'Pasport  Local  Number',
            'PASPORT_LOCAL_SER' => 'Pasport  Local  Ser',
            'PASPORT_LOCAL_DATE' => 'Pasport  Local  Date',
            'PASPORT_LOCAL_KEM' => 'Pasport  Local  Kem',
            'PASPORT_LOCAL_NUMB_PODRAZDEL' => 'Pasport  Local  Numb  Podrazdel',
            'PASPORT_ZAGRAN_TIP' => 'Pasport  Zagran  Tip',
            'PASPORT_ZAGRAN_NUMBER' => 'Pasport  Zagran  Number',
            'PASPORT_ZAGRAN_SER' => 'Pasport  Zagran  Ser',
            'PASPORT_ZAGRAN_DATE' => 'Pasport  Zagran  Date',
            'PASPORT_ZAGRAN_FINAL' => 'Pasport  Zagran  Final',
            'PASPORT_ZAGRAN_KEM' => 'Pasport  Zagran  Kem',
            'MANAGER_TRASH' => 'Manager  Trash',
            'DATE_TRASH' => 'Date  Trash',
            'CODE_1C' => 'Code 1 C',
            'MARKER_ON' => 'Marker  On',
            'ID_MANAGER_MARKER' => 'Id  Manager  Marker',
            'MARKER_DESCRIPTION' => 'Marker  Description',
            'ID_CATEGORY' => 'Id  Category',
            'ID_AGENT' => 'Id  Agent',
            'ADD_forma_oplati' => 'Add Forma Oplati',
            'ADD_date_finish' => 'Add Date Finish',
            'ADD_numb_dogovor' => 'Add Numb Dogovor',
            'ADD_crm_control' => 'Add Crm Control',
            'ADD_who' => 'Add Who',
            'IS_INTERNAL' => 'Is  Internal',
            'ADD_KOD1C_eko_korp' => 'Add  Kod1 C Eko Korp',
            'ADD_KOD1C_new' => 'Add  Kod1 C New',
            'ADD_KOD1C_general2' => 'Add  Kod1 C General2',
            'ADD_KOD1C_logistika' => 'Add  Kod1 C Logistika',
            'ADD_KOD1C_cuop' => 'Add  Kod1 C Cuop',
            'ADD_dog_orig' => 'Add Dog Orig',
            'ADD_scan_dog' => 'Add Scan Dog',
            'ADD_days_post' => 'Add Days Post',
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
     * Возвращает рабочий номер телефона ответственного по контрагенту менеджера.
     * @return string
     */
    public function getManagerPhone()
    {
        return $this->manager != null ? $this->manager->TELEPHON_JOB . (empty($this->manager->TELEPHON_HOME) ? '' : ', ' . $this->manager->TELEPHON_HOME) : '';
    }

    /**
     * Возвращает E-mail ответственного по контрагенту менеджера.
     * @return string
     */
    public function getManagerEmail()
    {
        return $this->manager != null ? $this->manager->e_mail : '';
    }

    /**
     * Невыполненные (в значении актуальные) задачи по контрагенту.
     * @return \yii\db\ActiveQuery
     */
    public function getTasksInProgress()
    {
        return $this->hasMany(foCompanyTasks::className(), ['ID_COMPANY' => 'ID_COMPANY'])
            ->select([
                'date' => new \yii\db\Expression('CONVERT(varchar(10), DATA_CONTACT, 120)'),
                'person' => 'CONTACT_MAN_NAME',
            ])
            ->distinct()
            ->where('ID_PRIZNAK_CONTACT <> ' . FreshOfficeAPI::TASKS_STATUS_ВЫПОЛНЕН)
            ->andWhere([
                'or',
                ['LIST_CONTACT_COMPANY.TRASH' => null],
                ['LIST_CONTACT_COMPANY.TRASH' => 0],
            ])
            ->joinWith(['contactPerson'])
            ->orderBy('date DESC')
            ->asArray();
    }

    /**
     * Возвращает 1% от суммы оборота с клиентом (условия: утилизация, приход). Округление не применяется.
     * @return double
     */
    public function getBalanceForCustomer()
    {
        return $this->hasOne(foFinances::className(), ['ID_COMPANY' => 'ID_COMPANY'])
            ->select('SUM(SUM_RUB)')
            ->where([
                'ID_SUB_PRIZNAK_MANY' => FreshOfficeAPI::FINANCES_PAYMENT_SIGN_УТИЛИЗАЦИЯ,
                'ID_NAPR' => FreshOfficeAPI::FINANCES_DIRECTION_ПРИХОД,
            ])
            ->groupBy('ID_COMPANY')
            ->scalar() / 100;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdGroupCompany()
    {
        return $this->hasOne(GROUPSCOMPANY::className(), ['id_group_company' => 'id_group_company']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIDVIDCOMPANY()
    {
        return $this->hasOne(VIDCOMPANY::className(), ['ID_VID_COMPANY' => 'ID_VID_COMPANY']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLISTCOMPANYEMAILSs()
    {
        return $this->hasMany(LISTCOMPANYEMAILS::className(), ['ID_COMPANY' => 'ID_COMPANY']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLISTCONTACTCOMPANies()
    {
        return $this->hasMany(LISTCONTACTCOMPANY::className(), ['ID_COMPANY' => 'ID_COMPANY']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLISTDOCUMENTSs()
    {
        return $this->hasMany(LISTDOCUMENTS::className(), ['ID_COMPANY' => 'ID_COMPANY']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLISTGOODSCOMPANies()
    {
        return $this->hasMany(LISTGOODSCOMPANY::className(), ['ID_COMPANY' => 'ID_COMPANY']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLISTSALETOVARs()
    {
        return $this->hasMany(LISTSALETOVAR::className(), ['ID_COMPANY' => 'ID_COMPANY']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLISTSTOCKDOCs()
    {
        return $this->hasMany(LISTSTOCKDOC::className(), ['ID_COMPANY' => 'ID_COMPANY']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLISTSTOCKDOCs0()
    {
        return $this->hasMany(LISTSTOCKDOC::className(), ['ID_COMPANY_SUPPLIER' => 'ID_COMPANY']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPHONELOGs()
    {
        return $this->hasMany(PHONELOG::className(), ['COMPANY_ID' => 'ID_COMPANY']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPHONEUSERLOGs()
    {
        return $this->hasMany(PHONEUSERLOG::className(), ['COMPANY_ID' => 'ID_COMPANY']);
    }
}
