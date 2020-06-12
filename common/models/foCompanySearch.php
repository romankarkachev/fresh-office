<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use backend\controllers\CounteragentsCrmController;

/**
 * foCompanySearch represents the model behind the search form of `common\models\foCompany`.
 */
class foCompanySearch extends foCompany
{
    /**
     * @var string поле для универсального поиска
     */
    public $searchEntire;

    /**
     * {@inheritdoc}searchEntire
     */
    public function rules()
    {
        return [
            [['ID_COMPANY', 'ID_MANAGER', 'ID_OFFICE', 'ID_VID_COMPANY', 'id_group_company', 'YUR_FIZ', 'ID_LIST_STATUS_COMPANY', 'TRASH', 'MARKER_ON', 'ID_MANAGER_MARKER', 'ID_CATEGORY', 'ID_AGENT', 'IS_INTERNAL', 'ADD_days_post'], 'integer'],
            [
                [
                    'OKPO', 'INN', 'COMPANY_NAME', 'ADRES', 'CITY', 'DATA_INPUT', 'ROD_DEYATEL', 'URL_COMPANY', 'ID_CH',
                    'PUBLIC_COMPANY', 'DOP_INF', 'INFORM_IN_COMPANY', 'DR_COMPANY', 'PROF_HOLIDAY', 'MANAGER_NAME_CREATER_COMPANY',
                    'COUNTRY_COMPANY', 'FORM_SOBST_COMPANY', 'FAM_FIZ', 'NAME_FIZ', 'OTCH_FIZ', 'FAM_LAT_FIZ', 'NAME_LAT_FIZ',
                    'REGION', 'MESTO_RABOT_FIZ', 'DOLGNOST_RABOT_FIZ', 'ADDRESS_RABOT_FIZ', 'POL_ANKT', 'SEM_POLOJ_ANKT',
                    'M_RJD_ANKT', 'COUNTRY_RJD_ANKT', 'GRAJD_ANKT', 'PASPORT_LOCAL_NUMBER', 'PASPORT_LOCAL_SER', 'PASPORT_LOCAL_DATE',
                    'PASPORT_LOCAL_KEM', 'PASPORT_LOCAL_NUMB_PODRAZDEL', 'PASPORT_ZAGRAN_TIP', 'PASPORT_ZAGRAN_NUMBER',
                    'PASPORT_ZAGRAN_SER', 'PASPORT_ZAGRAN_DATE', 'PASPORT_ZAGRAN_FINAL', 'PASPORT_ZAGRAN_KEM', 'MANAGER_TRASH',
                    'DATE_TRASH', 'CODE_1C', 'MARKER_DESCRIPTION', 'ADD_forma_oplati', 'ADD_date_finish', 'ADD_numb_dogovor',
                    'ADD_crm_control', 'ADD_who', 'ADD_KOD1C_eko_korp', 'ADD_KOD1C_new', 'ADD_KOD1C_general2', 'ADD_KOD1C_logistika',
                    'ADD_KOD1C_cuop', 'ADD_dog_orig', 'ADD_scan_dog', 'ADD_KOD1C_nok', 'ADD_KOD1C_tmp', 'ADD_KOD_1C_sex',
                    'ADD_ADD_KOD1C_sex', 'ADD_KOD1C_sex', 'searchEntire'
                ], 'safe'
            ],
            ['searchEntire', 'trim'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'searchEntire' => 'Универсальный поиск',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param $route string URL для перехода в список записей
     * @return ActiveDataProvider
     */
    public function search($params, $route = CounteragentsCrmController::URL_ROOT)
    {
        $tableName = foCompany::tableName();
        $query = foCompany::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => $route,
                'pageSize' => 50,
            ],
            'sort' => [
                'route' => $route,
                'defaultOrder' => ['DATA_INPUT' => SORT_DESC],
                'attributes' => [
                    'ID_COMPANY' => [
                        'asc' => [$tableName . '.ID_COMPANY' => SORT_ASC],
                        'desc' => [$tableName . '.ID_COMPANY' => SORT_DESC],
                    ],
                    'OKPO',
                    'INN',
                    'COMPANY_NAME',
                    'ID_MANAGER',
                    'DATA_INPUT' => [
                        'asc' => [$tableName . '.DATA_INPUT' => SORT_ASC],
                        'desc' => [$tableName . '.DATA_INPUT' => SORT_DESC],
                    ],
                    'managerName' => [
                        'asc' => [foManagers::tableName() . '.MANAGER_NAME' => SORT_ASC],
                        'desc' => [foManagers::tableName() . '.MANAGER_NAME' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['manager']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if (Yii::$app->user->can('sales_department_manager')) {
            // для менеджеров доступ только к их контрагентам
            $profile = Yii::$app->user->identity->profile;
            if (!empty($profile) && !empty($profile->fo_id)) {
                $trusted = UsersTrusted::find()->select('fo_id')->joinWith('userProfile', false)->where(['section' => UsersTrusted::SECTION_КОНТРАГЕНТЫ, 'trusted_id' => Yii::$app->user->id])->column();
                $condition = [$tableName . '.ID_MANAGER' => $profile->fo_id];
                if (count($trusted) > 0) {
                    $condition = ArrayHelper::merge([
                        'or',
                        [$tableName . '.ID_MANAGER' => $trusted],
                    ], [$condition]);
                }
                $query->andWhere($condition);
                unset($trusted);
            }
            else {
                // если нет профиля или пользователь Fresh Office не сопоставлен, тогда вообще ничего не отдаем
                $query->where('0=1');
            }

            unset($profile);
        }
        else {
            $query->andFilterWhere([
                $tableName . '.ID_MANAGER' => $this->ID_MANAGER,
            ]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'ID_OFFICE' => $this->ID_OFFICE,
            'DATA_INPUT' => $this->DATA_INPUT,
            'ID_VID_COMPANY' => $this->ID_VID_COMPANY,
            'id_group_company' => $this->id_group_company,
            'YUR_FIZ' => $this->YUR_FIZ,
            'ID_LIST_STATUS_COMPANY' => $this->ID_LIST_STATUS_COMPANY,
            'DR_COMPANY' => $this->DR_COMPANY,
            'PROF_HOLIDAY' => $this->PROF_HOLIDAY,
            'TRASH' => $this->TRASH,
            'PASPORT_LOCAL_DATE' => $this->PASPORT_LOCAL_DATE,
            'PASPORT_ZAGRAN_DATE' => $this->PASPORT_ZAGRAN_DATE,
            'PASPORT_ZAGRAN_FINAL' => $this->PASPORT_ZAGRAN_FINAL,
            'DATE_TRASH' => $this->DATE_TRASH,
            'MARKER_ON' => $this->MARKER_ON,
            'ID_MANAGER_MARKER' => $this->ID_MANAGER_MARKER,
            'ID_CATEGORY' => $this->ID_CATEGORY,
            'ID_AGENT' => $this->ID_AGENT,
            'ADD_date_finish' => $this->ADD_date_finish,
            'IS_INTERNAL' => $this->IS_INTERNAL,
            'ADD_dog_orig' => $this->ADD_dog_orig,
            'ADD_scan_dog' => $this->ADD_scan_dog,
            'ADD_days_post' => $this->ADD_days_post,
        ]);

        if (!empty($this->searchEntire)) {
            $query->andFilterWhere([
                'or',
                ['like', $tableName . '.ID_COMPANY', $this->searchEntire],
                ['like', 'INN', $this->searchEntire],
                ['like', 'COMPANY_NAME', $this->searchEntire],
            ]);
        }
        else {
            $query->andFilterWhere([$tableName . '.ID_COMPANY' => $this->ID_COMPANY])
                ->andFilterWhere(['like', 'INN', $this->INN])
                ->andFilterWhere(['like', 'COMPANY_NAME', $this->COMPANY_NAME]);
        }

        $query->andFilterWhere(['like', 'OKPO', $this->OKPO])
            ->andFilterWhere(['like', 'ADRES', $this->ADRES])
            ->andFilterWhere(['like', 'CITY', $this->CITY])
            ->andFilterWhere(['like', 'ROD_DEYATEL', $this->ROD_DEYATEL])
            ->andFilterWhere(['like', 'URL_COMPANY', $this->URL_COMPANY])
            ->andFilterWhere(['like', 'ID_CH', $this->ID_CH])
            ->andFilterWhere(['like', 'PUBLIC_COMPANY', $this->PUBLIC_COMPANY])
            ->andFilterWhere(['like', 'DOP_INF', $this->DOP_INF])
            ->andFilterWhere(['like', 'INFORM_IN_COMPANY', $this->INFORM_IN_COMPANY])
            ->andFilterWhere(['like', 'MANAGER_NAME_CREATER_COMPANY', $this->MANAGER_NAME_CREATER_COMPANY])
            ->andFilterWhere(['like', 'COUNTRY_COMPANY', $this->COUNTRY_COMPANY])
            ->andFilterWhere(['like', 'FORM_SOBST_COMPANY', $this->FORM_SOBST_COMPANY])
            ->andFilterWhere(['like', 'FAM_FIZ', $this->FAM_FIZ])
            ->andFilterWhere(['like', 'NAME_FIZ', $this->NAME_FIZ])
            ->andFilterWhere(['like', 'OTCH_FIZ', $this->OTCH_FIZ])
            ->andFilterWhere(['like', 'FAM_LAT_FIZ', $this->FAM_LAT_FIZ])
            ->andFilterWhere(['like', 'NAME_LAT_FIZ', $this->NAME_LAT_FIZ])
            ->andFilterWhere(['like', 'REGION', $this->REGION])
            ->andFilterWhere(['like', 'MESTO_RABOT_FIZ', $this->MESTO_RABOT_FIZ])
            ->andFilterWhere(['like', 'DOLGNOST_RABOT_FIZ', $this->DOLGNOST_RABOT_FIZ])
            ->andFilterWhere(['like', 'ADDRESS_RABOT_FIZ', $this->ADDRESS_RABOT_FIZ])
            ->andFilterWhere(['like', 'POL_ANKT', $this->POL_ANKT])
            ->andFilterWhere(['like', 'SEM_POLOJ_ANKT', $this->SEM_POLOJ_ANKT])
            ->andFilterWhere(['like', 'M_RJD_ANKT', $this->M_RJD_ANKT])
            ->andFilterWhere(['like', 'COUNTRY_RJD_ANKT', $this->COUNTRY_RJD_ANKT])
            ->andFilterWhere(['like', 'GRAJD_ANKT', $this->GRAJD_ANKT])
            ->andFilterWhere(['like', 'PASPORT_LOCAL_NUMBER', $this->PASPORT_LOCAL_NUMBER])
            ->andFilterWhere(['like', 'PASPORT_LOCAL_SER', $this->PASPORT_LOCAL_SER])
            ->andFilterWhere(['like', 'PASPORT_LOCAL_KEM', $this->PASPORT_LOCAL_KEM])
            ->andFilterWhere(['like', 'PASPORT_LOCAL_NUMB_PODRAZDEL', $this->PASPORT_LOCAL_NUMB_PODRAZDEL])
            ->andFilterWhere(['like', 'PASPORT_ZAGRAN_TIP', $this->PASPORT_ZAGRAN_TIP])
            ->andFilterWhere(['like', 'PASPORT_ZAGRAN_NUMBER', $this->PASPORT_ZAGRAN_NUMBER])
            ->andFilterWhere(['like', 'PASPORT_ZAGRAN_SER', $this->PASPORT_ZAGRAN_SER])
            ->andFilterWhere(['like', 'PASPORT_ZAGRAN_KEM', $this->PASPORT_ZAGRAN_KEM])
            ->andFilterWhere(['like', 'MANAGER_TRASH', $this->MANAGER_TRASH])
            ->andFilterWhere(['like', 'CODE_1C', $this->CODE_1C])
            ->andFilterWhere(['like', 'MARKER_DESCRIPTION', $this->MARKER_DESCRIPTION])
            ->andFilterWhere(['like', 'ADD_forma_oplati', $this->ADD_forma_oplati])
            ->andFilterWhere(['like', 'ADD_numb_dogovor', $this->ADD_numb_dogovor])
            ->andFilterWhere(['like', 'ADD_crm_control', $this->ADD_crm_control])
            ->andFilterWhere(['like', 'ADD_who', $this->ADD_who])
            ->andFilterWhere(['like', 'ADD_KOD1C_eko_korp', $this->ADD_KOD1C_eko_korp])
            ->andFilterWhere(['like', 'ADD_KOD1C_new', $this->ADD_KOD1C_new])
            ->andFilterWhere(['like', 'ADD_KOD1C_general2', $this->ADD_KOD1C_general2])
            ->andFilterWhere(['like', 'ADD_KOD1C_logistika', $this->ADD_KOD1C_logistika])
            ->andFilterWhere(['like', 'ADD_KOD1C_cuop', $this->ADD_KOD1C_cuop])
            ->andFilterWhere(['like', 'ADD_KOD1C_nok', $this->ADD_KOD1C_nok])
            ->andFilterWhere(['like', 'ADD_KOD1C_tmp', $this->ADD_KOD1C_tmp])
            ->andFilterWhere(['like', 'ADD_KOD_1C_sex', $this->ADD_KOD_1C_sex])
            ->andFilterWhere(['like', 'ADD_ADD_KOD1C_sex', $this->ADD_ADD_KOD1C_sex])
            ->andFilterWhere(['like', 'ADD_KOD1C_sex', $this->ADD_KOD1C_sex]);

        return $dataProvider;
    }
}
