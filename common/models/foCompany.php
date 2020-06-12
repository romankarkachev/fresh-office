<?php

namespace common\models;

use Yii;
use yii\data\ArrayDataProvider;

/**
 * This is the model class for table "COMPANY".
 *
 * @property integer $ID_COMPANY
 * @property string $OKPO
 * @property string $INN
 * @property string $COMPANY_NAME
 * @property string $ADRES
 * @property string $CITY
 * @property integer $ID_MANAGER ответственный (таблица [MANAGERS])
 * @property integer $ID_OFFICE
 * @property string $DATA_INPUT
 * @property integer $ID_VID_COMPANY тип (таблица [VID_COMPANY], реквизит "Тип" в карточке, пример: "Новый исходящий")
 * @property string $ROD_DEYATEL
 * @property integer $id_group_company
 * @property string $URL_COMPANY
 * @property string $ID_CH
 * @property string $PUBLIC_COMPANY
 * @property string $DOP_INF Примечание
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
 * @property string $listCuratorCompanyManagerName
 * @property string $companyDetailsInn
 * @property string $companyDetailsKpp
 *
 * @property foManagers $manager
 * @property array $tasksInProgress выполняемые и запланированные задачи по контрагенту
 * @property double $balanceForCustomer количество баллов у клиента, рассчитанное от оборота с ним
 *
 * @property foListCuratorCompany $listCuratorCompany связка какого-то менеджера и контрагента
 * @property foManagers $listCuratorCompanyManager какой-то менеджер контрагента
 * @property foCompanyDetails $companyDetails
 * @property \yii\data\ArrayDataProvider $contactPersonsAsDataProvider контактные лица, модели foCompanyContactPersons
 * @property \yii\data\ArrayDataProvider $tasksAsDataProvider задачи, модели Tasks
 * @property \yii\data\ArrayDataProvider $tasksCrmAsDataProvider задачи, модели foTasks
 * @property \yii\data\ArrayDataProvider $ecoProjectsAsDataProvider проекты по экологии, модели EcoProjects
 * @property \yii\data\ArrayDataProvider $ecoContractsAsDataProvider договоры сопровождения по экологии, модели EcoMc
 * @property \yii\data\ArrayDataProvider $callsAsDataProvider проекты CRM, модели foProjects
 * @property \yii\data\ArrayDataProvider $projectsAsDataProvider проекты CRM, модели foProjects
 * @property \yii\data\ArrayDataProvider $incomingMailAsDataProvider входящая корреспонденция, модели IncomingMail
 * @property \yii\data\ArrayDataProvider $edfAsDataProvider документооборот, модели Edf
 *
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
            //[['id_group_company'], 'exist', 'skipOnError' => true, 'targetClass' => GROUPSCOMPANY::className(), 'targetAttribute' => ['id_group_company' => 'id_group_company']],
            [['ID_MANAGER'], 'exist', 'skipOnError' => true, 'targetClass' => foManagers::class, 'targetAttribute' => ['ID_MANAGER' => 'ID_MANAGER']],
            //[['ID_VID_COMPANY'], 'exist', 'skipOnError' => true, 'targetClass' => VIDCOMPANY::className(), 'targetAttribute' => ['ID_VID_COMPANY' => 'ID_VID_COMPANY']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID_COMPANY' => 'ID',
            'OKPO' => 'ОКПО',
            'INN' => 'ИНН',
            'COMPANY_NAME' => 'Наименование',
            'ADRES' => 'Адрес',
            'CITY' => 'Город',
            'ID_MANAGER' => 'Ответственный',
            'ID_OFFICE' => 'Id  Office',
            'DATA_INPUT' => 'Data  Input',
            'ID_VID_COMPANY' => 'Id  Vid  Company',
            'ROD_DEYATEL' => 'Rod  Deyatel',
            'id_group_company' => 'Id Group Company',
            'URL_COMPANY' => 'Url  Company',
            'ID_CH' => 'Id  Ch',
            'PUBLIC_COMPANY' => 'Public  Company',
            'DOP_INF' => 'Примечание',
            'YUR_FIZ' => 'Yur  Fiz',
            'ID_LIST_STATUS_COMPANY' => 'Id  List  Status  Company',
            'INFORM_IN_COMPANY' => 'Источник',
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
            'REGION' => 'Регион',
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
            'ADD_forma_oplati' => 'Форма оплаты',
            'ADD_date_finish' => 'Add Date Finish',
            'ADD_numb_dogovor' => 'Add Numb Dogovor',
            'ADD_crm_control' => 'Add Crm Control',
            'ADD_who' => 'Исполнитель',
            'IS_INTERNAL' => 'Is  Internal',
            'ADD_KOD1C_eko_korp' => 'Add  Kod1 C Eko Korp',
            'ADD_KOD1C_new' => 'Add  Kod1 C New',
            'ADD_KOD1C_general2' => 'Add  Kod1 C General2',
            'ADD_KOD1C_logistika' => 'Add  Kod1 C Logistika',
            'ADD_KOD1C_cuop' => 'Add  Kod1 C Cuop',
            'ADD_dog_orig' => 'Add Dog Orig',
            'ADD_scan_dog' => 'Add Scan Dog',
            'ADD_days_post' => 'Отсрочка',
            // вычисляемые поля
            'managerName' => 'Ответственный',
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
     * @return \yii\db\ActiveQuery
     */
    public function getListCuratorCompany()
    {
        return $this->hasOne(foListCuratorCompany::class, ['ID_COMPANY' => 'ID_COMPANY']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getListCuratorCompanyManager()
    {
        return $this->hasOne(foManagers::class, ['ID_MANAGER' => 'ID_MANAGER'])->via('listCuratorCompany');
    }

    /**
     * Возвращает имя какого-то менеджера.
     * @return string
     */
    public function getListCuratorCompanyManagerName()
    {
        return $this->listCuratorCompanyManager != null ? $this->listCuratorCompanyManager->MANAGER_NAME : '';
    }

    /**
     * Невыполненные (в значении актуальные) задачи по контрагенту.
     * @return \yii\db\ActiveQuery
     */
    public function getTasksInProgress()
    {
        return $this->hasMany(foCompanyTasks::class, ['ID_COMPANY' => 'ID_COMPANY'])
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
     * Выборка из CRM задач контрагента. Используется в карточке контрагента.
     * @return ArrayDataProvider
     */
    public function getTasksAsDataProvider()
    {
        $tasksTableName = Tasks::tableName();

        return new ArrayDataProvider([
            'modelClass' => 'common\models\Tasks',
            'key' => 'id',
            'allModels' => Tasks::find()->where([$tasksTableName . '.fo_ca_id' => $this->ID_COMPANY])->all(),
            'pagination' => ['pageSize' => 50],
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => TasksSearch::sortAttributes(),
            ],
        ]);
    }

    /**
     * Выборка задач контрагента. Используется в карточке контрагента.
     * @return ArrayDataProvider
     */
    public function getTasksCrmAsDataProvider()
    {
        $tasksTableName = foCompanyTasks::tableName();

        return new ArrayDataProvider([
            'key' => 'ID_CONTACT',
            'allModels' => foCompanyTasks::find()->select([
                'id' => 'ID_CONTACT',
                'created_at' => new \yii\db\Expression('CONVERT(varchar(10), DATA_CONTACT, 120)'),
                'contactPersonName' => 'CONTACT_MAN_NAME',
                'description' => 'PRIMECHANIE',
                'comment' => 'REZULTAT_CONTACT',
            ])
                ->where([$tasksTableName . '.ID_COMPANY' => $this->ID_COMPANY])
                ->andWhere([
                    'or',
                    [$tasksTableName . '.TRASH' => null],
                    [$tasksTableName . '.TRASH' => 0],
                ])
                ->joinWith(['contactPerson'])
                ->asArray()->all(),
            'pagination' => ['pageSize' => 50],
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'created_at',
                    'contactPersonName',
                    'description',
                    'comment',
                ],
            ],
        ]);
    }

    /**
     * Выборка проектов по экологии в процессе выполнения.
     * @return ArrayDataProvider
     */
    public function getEcoProjectsAsDataProvider()
    {
        return new ArrayDataProvider([
            //'modelClass' => 'common\models\EcoProjects',
            'key' => 'id',
            'allModels' => EcoProjects::find()->select([
                EcoProjects::tableName() . '.id',
                'created_at',
                'date_start',
                'date_close_plan',
                // можно и организации, но они нигде не заполняются
                //'organizationName' => Organizations::tableName() . '.name',
                'typeName' => EcoTypes::tableName() . '.name',
            ])->where([
                'closed_at' => null,
                'ca_id' => $this->ID_COMPANY,
            ])->joinWith([
                // можно и организации, но они нигде не заполняются
                //'organization',
                'type',
            ])->asArray()->all(),
            'pagination' => ['pageSize' => 50],
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'created_at',
                    'date_start',
                    'date_close_plan',
                    // можно и организации, но они нигде не заполняются
                    //'organizationName',
                    'typeName',
                ],
            ],
        ]);
    }

    /**
     * Выборка договоров сопровождения по экологии.
     * @return ArrayDataProvider
     */
    public function getEcoContractsAsDataProvider()
    {
        return new ArrayDataProvider([
            'modelClass' => 'common\models\EcoMc',
            'key' => 'id',
            'allModels' => EcoMc::find()->where([
                'fo_ca_id' => $this->ID_COMPANY,
            ])
                // можно и организации, но они нигде не заполняются
                //->joinWith(['organization'])
                ->asArray()->all(),
            'pagination' => ['pageSize' => 50],
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'created_at',
                    // можно и организации, но они нигде не заполняются
                    //'organizationName',
                    'date_start',
                    'date_finish',
                    'amount',
                ],
            ],
        ]);
    }

    /**
     * Выборка звонков контрагента.
     * @return ArrayDataProvider
     */
    public function getCallsAsDataProvider()
    {
        return new ArrayDataProvider([
            'key' => 'id',
            'allModels' => pbxCalls::find()->where([
                'fo_ca_id' => $this->ID_COMPANY,
            ])->joinWith(['srcEmployee', 'website'])->all(),
            'pagination' => ['pageSize' => 50],
            'sort' => [
                'defaultOrder' => ['calldate' => SORT_DESC],
                'attributes' => [
                    'calldate',
                    'clid',
                    'src',
                    'dst',
                    'dcontext',
                    'websiteName' => [
                        'asc' => ['websites.name' => SORT_ASC],
                        'desc' => ['websites.name' => SORT_DESC],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Выборка проектов по контрагенту.
     * @return ArrayDataProvider
     */
    public function getProjectsAsDataProvider()
    {
        return new ArrayDataProvider([
            'modelClass' => 'common\models\foProjects',
            'key' => 'ID_COMPANY',
            'allModels' => foProjects::find()->where([
                'ID_COMPANY' => $this->ID_COMPANY,
            ])->joinWith(['type', 'state'])->all(),
            'pagination' => ['pageSize' => 50],
            'sort' => [
                'defaultOrder' => ['ADD_vivozdate' => SORT_DESC],
                'attributes' => [
                    'ID_LIST_PROJECT_COMPANY',
                    'DATE_CREATE_PROGECT' => [
                        'asc' => ['DATE_CREATE_PROGECT' => SORT_ASC],
                        'desc' => ['DATE_CREATE_PROGECT' => SORT_DESC],
                    ],
                    'typeName' => [
                        'asc' => ['NAME_PROJECT' => SORT_ASC],
                        'desc' => ['NAME_PROJECT' => SORT_DESC],
                    ],
                    'stateName' => [
                        'asc' => ['PRIZNAK_PROJECT' => SORT_ASC],
                        'desc' => ['PRIZNAK_PROJECT' => SORT_DESC],
                    ],
                    'ADD_vivozdate' => [
                        'asc' => ['ADD_vivozdate' => SORT_ASC],
                        'desc' => ['ADD_vivozdate' => SORT_DESC],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Выборка входящей от контрагента корреспонденции.
     * @return ArrayDataProvider
     */
    public function getIncomingMailAsDataProvider()
    {
        return new ArrayDataProvider([
            'modelClass' => 'common\models\IncomingMail',
            'key' => 'id',
            'allModels' => IncomingMail::find()->where([
                'ca_src' => IncomingMail::CA_SOURCES_FRESH_OFFICE,
                'ca_id' => $this->ID_COMPANY,
            ])->joinWith(['createdByProfile', 'type', 'organization', 'receiverProfile'])->all(),
            'pagination' => ['pageSize' => 50],
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => ['created_at'],
            ],
        ]);
    }

    /**
     * Выборка документооборота с контрагентом.
     * @return ArrayDataProvider
     */
    public function getEdfAsDataProvider()
    {
        return new ArrayDataProvider([
            'modelClass' => 'common\models\Edf',
            'key' => 'id',
            'allModels' => Edf::find()->where([
                'fo_ca_id' => $this->ID_COMPANY,
                'type_id' => DocumentsTypes::TYPE_ДОГОВОР,
            ])->orderBy('doc_date_expires')->all(),
            'pagination' => ['pageSize' => 50],
            'sort' => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => ['created_at'],
            ],
        ]);
    }

    /**
     * Возвращает 1% от суммы оборота с клиентом (условия: утилизация, приход). Округление не применяется.
     * @return double
     */
    public function getBalanceForCustomer()
    {
        return $this->hasOne(foFinances::class, ['ID_COMPANY' => 'ID_COMPANY'])
            ->select('SUM(SUM_RUB)')
            ->where([
                'ID_SUB_PRIZNAK_MANY' => FreshOfficeAPI::FINANCES_PAYMENT_SIGN_УТИЛИЗАЦИЯ,
                'ID_NAPR' => FreshOfficeAPI::FINANCES_DIRECTION_ПРИХОД,
            ])
            ->groupBy('ID_COMPANY')
            ->scalar() / 100;
    }

    /**
     * @return ArrayDataProvider
     */
    public function getContactPersonsAsDataProvider()
    {
        return new ArrayDataProvider([
            'key' => 'id',
            'allModels' => DirectMSSQLQueries::fetchCounteragentsContactPersons($this->ID_COMPANY),
            'pagination' => false,
            'sort' => [
                'defaultOrder' => ['name' => SORT_ASC],
                'attributes' => [
                    'id',
                    'name',
                    'phones',
                    'emails',
                ],
            ],
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContactPersons()
    {
        return $this->hasMany(foCompanyContactPersons::class, ['ID_COMPANY' => 'ID_COMPANY']);
    }

    /**
     * Возвращает реквизиты контрагента.
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyDetails()
    {
        return $this->hasOne(foCompanyDetails::class, ['ID_COMPANY' => 'ID_COMPANY']);
    }

    /**
     * Возвращает ИНН контрагента.
     * @return string
     */
    public function getCompanyDetailsInn()
    {
        return !empty($this->companyDetails) ? $this->companyDetails->INN : '';
    }

    /**
     * Возвращает КПП контрагента.
     * @return string
     */
    public function getCompanyDetailsKpp()
    {
        return !empty($this->companyDetails) ? $this->companyDetails->KPP : '';
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
