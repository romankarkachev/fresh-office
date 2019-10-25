<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\controllers\EdfController;
use yii\helpers\ArrayHelper;

/**
 * EdfSearch represents the model behind the search form about `common\models\Edf`.
 */
class EdfSearch extends Edf
{
    /**
     * Группы статусов электронных документов
     */
    const CLAUSE_STATE_DEFAULT = 1;
    const CLAUSE_STATE_НА_ПОДПИСИ = 2;
    const CLAUSE_STATE_ВСЕ_КРОМЕ_ЗАВЕРШЕННЫХ = 4;
    const CLAUSE_STATE_ВСЕ = 3;

    /**
     * @var integer поле отбора по полю "Типовой"
     */
    public $searchTypical;

    /**
     * @var integer поле отбора по полю "Скан"
     */
    public $searchScan;

    /**
     * @var integer поле отбора по полю "Оригинал"
     */
    public $searchOriginal;

    /**
     * @var integer поле отбора по полю "Статус" в виде нескольких значений, сгруппированных между собой
     */
    public $searchGroupStates;

    /**
     * @var bool отбор только просроченныз
     */
    public $searchOutdatedOnly;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'type_id', 'parent_id', 'ct_id', 'state_id', 'org_id', 'ba_id', 'manager_id', 'cp_id', 'fo_ca_id', 'is_typical_form', 'is_received_scan', 'is_received_original', 'searchGroupStates', 'searchOutdatedOnly'], 'integer'],
            [['doc_num', 'doc_date', 'doc_date_expires', 'basis', 'req_name_full', 'req_name_short', 'req_ogrn', 'req_inn', 'req_kpp', 'req_address_j', 'req_address_f', 'req_an', 'req_bik', 'req_bn', 'req_ca', 'req_phone', 'req_email', 'req_dir_post', 'req_dir_name', 'req_dir_name_of', 'req_dir_name_short', 'req_dir_name_short_of', 'files_full_path', 'comment', 'searchTypical', 'searchScan', 'searchOriginal'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'searchTypical' => 'Типовой',
            'searchScan' => 'Скан',
            'searchOriginal' => 'Оригинал',
            'searchGroupStates' => 'Группы статусов',
            'searchOutdatedOnly' => 'Только просроченные',
        ]);
    }

    /**
     * Возвращает массив с идентификаторами статусов по группам.
     * @return array
     */
    public static function fetchGroupStatesIds()
    {
        return [
            [
                'id' => self::CLAUSE_STATE_DEFAULT,
                'name' => 'По умолчанию',
                'hint' => 'Заявка',
            ],
            [
                'id' => self::CLAUSE_STATE_НА_ПОДПИСИ,
                'name' => 'На подписи',
                'hint' => 'Как на подписи у руководства, так и на подписи у заказчика',
            ],
            [
                'id' => self::CLAUSE_STATE_ВСЕ_КРОМЕ_ЗАВЕРШЕННЫХ,
                'name' => 'Кроме завершенных',
                'hint' => 'Все документы, исключая завершенные',
            ],
            [
                'id' => self::CLAUSE_STATE_ВСЕ,
                'name' => 'Все',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $edfTableName = Edf::tableName();

        $query = Edf::find()->select([
            '*',
            'id' => $edfTableName . '.`id`',
            'stateChangedAt' => EdfStatesHistory::find()->select('created_at')->where('ed_id = ' . $edfTableName . '.`id`')->orderBy('`created_at` DESC')->limit(1),
            'unreadMessagesCount' => '(
                SELECT COUNT(`edf_dialogs`.`id`) FROM `edf_dialogs`
                WHERE `edf_dialogs`.`ed_id` = `edf`.`id` AND `edf_dialogs`.`read_at` IS NULL
            )',
        ]);

        if (Yii::$app->user->can('sales_department_manager') || Yii::$app->user->can('ecologist') || Yii::$app->user->can('ecologist_head') || Yii::$app->user->can('tenders_manager')) {
            // для начала проверяем наличие отделов у данного пользователя
            // если он не включен ни в один отдел, то будет произведен отбор только его собственных документов
            $departments = UsersDepartments::find()->select('department_id')->where(['user_id' => Yii::$app->user->id])->column();
            if (empty($departments)) {
                $query->orWhere([$edfTableName . '.manager_id' => Yii::$app->user->id]);
            }
            else {
                $query->orWhere([$edfTableName . '.manager_id' => UsersDepartments::find()->select('user_id')->where(['department_id' => $departments])]);
            }

            /*
            // запрос только тех документов, которые курируются менеджерами своих отделов
            $query->orWhere([$edfTableName . '.manager_id' => UsersDepartments::find()->select('user_id')->where(['department_id' => UsersDepartments::find()->select('department_id')->where(['user_id' => Yii::$app->user->id])])]);

            $query->where([
                'or',
                [$edfTableName . '.manager_id' => Yii::$app->user->id],
                [$edfTableName . '.manager_id' => UsersDepartments::find()->select('user_id')->where(['department_id' => UsersDepartments::find()->select('department_id')->where(['user_id' => Yii::$app->user->id])])],
            ]);
            */

            if (Yii::$app->user->can('ecologist_head')) {
                // для начальника экологии отбор тех пакетов, где менеджером указан пользователь с ролью эколога
                $query->joinWith(['managerRole']);
                $query->orWhere([
                    'item_name' => 'ecologist',
                ]);
            }
        }

        // для пользователя с ролью "Делопроизводство" доступны документы только полностью оформленные
        if (Yii::$app->user->can('edf') || Yii::$app->user->can('operator_head')) {
            $query->where($edfTableName . '.state_id > ' . EdfStates::STATE_ЧЕРНОВИК);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => EdfController::ROOT_URL_FOR_SORT_PAGING,
                'pageSize' => 50,
            ],
            'sort' => [
                'route' => EdfController::ROOT_URL_FOR_SORT_PAGING,
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'id',
                    'created_at' => [
                        'asc' => [$edfTableName . '.created_at' => SORT_ASC],
                        'desc' => [$edfTableName . '.created_at' => SORT_DESC],
                    ],
                    'created_by',
                    'type_id',
                    'parent_id',
                    'ct_id',
                    'state_id',
                    'org_id',
                    'ba_id',
                    'manager_id',
                    'cp_id',
                    'is_typical_form',
                    'doc_num',
                    'doc_date',
                    'doc_date_expires',
                    'ca_name',
                    'ca_basis',
                    'req_name_full',
                    'req_name_short',
                    'req_ogrn',
                    'req_inn',
                    'req_kpp',
                    'req_address_j',
                    'req_address_f',
                    'req_an',
                    'req_bik',
                    'req_bn',
                    'req_ca',
                    'req_phone',
                    'req_email',
                    'req_dir_post',
                    'req_dir_name',
                    'req_dir_name_of',
                    'req_dir_name_short',
                    'req_dir_name_short_of',
                    'is_received_scan',
                    'is_received_original',
                    'comment',
                    'createdByProfileName' => [
                        'asc' => ['createdProfile.name' => SORT_ASC],
                        'desc' => ['createdProfile.name' => SORT_DESC],
                    ],
                    'typeName' => [
                        'asc' => [DocumentsTypes::tableName() . '.name' => SORT_ASC],
                        'desc' => [DocumentsTypes::tableName() . '.name' => SORT_DESC],
                    ],
                    'contractTypeName' => [
                        'asc' => [ContractTypes::tableName() . '.name' => SORT_ASC],
                        'desc' => [ContractTypes::tableName() . '.name' => SORT_DESC],
                    ],
                    'stateName' => [
                        'asc' => [EdfStates::tableName() . '.name' => SORT_ASC],
                        'desc' => [EdfStates::tableName() . '.name' => SORT_DESC],
                    ],
                    'organizationName' => [
                        'asc' => [Organizations::tableName() . '.name' => SORT_ASC],
                        'desc' => [Organizations::tableName() . '.name' => SORT_DESC],
                    ],
                    'baAn' => [
                        'asc' => [OrganizationsBas::tableName() . '.bank_an' => SORT_ASC],
                        'desc' => [OrganizationsBas::tableName() . '.bank_an' => SORT_DESC],
                    ],
                    'managerProfileName' => [
                        'asc' => ['managerProfile.name' => SORT_ASC],
                        'desc' => ['managerProfile.name' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $joinWith = ['createdByProfile', 'type', 'contractType', 'state', 'organization', 'bankAccount', 'managerProfile'];
        if (!empty($this->type_id) && $this->type_id == DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ) {
            $joinWith[] = 'parent';
            $query->select = ArrayHelper::merge($query->select, [
                'created_at' => $edfTableName . '.`created_at`',
                'created_by' => $edfTableName . '.`created_by`',
                'manager_id' => $edfTableName . '.`manager_id`',
                'state_id' => $edfTableName . '.`state_id`',
                'org_id' => $edfTableName . '.`org_id`',
                'doc_num' => $edfTableName . '.`doc_num`',
                'req_name_short' => $edfTableName . '.`req_name_short`',
                'parent_id' => $edfTableName . '.`parent_id`',
            ]);
        }
        $query->joinWith($joinWith);

        if (null !== Yii::$app->request->get('outdated')) {
            // пользователь выполняет отбор документов с учетом того, что отображать придется только просроченные
            $this->searchOutdatedOnly = true;
        }

        // по умолчанию отображаются документы в статусе "Заявка"
        if (empty($this->searchGroupStates) && empty($this->state_id)) {
            $this->searchGroupStates = self::CLAUSE_STATE_DEFAULT;
        }

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        switch ($this->searchGroupStates) {
            case self::CLAUSE_STATE_DEFAULT:
                $query->andFilterWhere([
                    $edfTableName . '.state_id' => [EdfStates::STATE_ЗАЯВКА,EdfStates::STATE_ОТКАЗ],
                ]);
                break;
            case self::CLAUSE_STATE_НА_ПОДПИСИ:
                $query->andFilterWhere([
                    $edfTableName . '.state_id' => [
                        EdfStates::STATE_НА_ПОДПИСИ_У_РУКОВОДСТВА,
                        EdfStates::STATE_НА_ПОДПИСИ_У_ЗАКАЗЧИКА,
                    ],
                ]);
                break;
            case self::CLAUSE_STATE_ВСЕ_КРОМЕ_ЗАВЕРШЕННЫХ:
                $query->andWhere($edfTableName . '.state_id <> ' . EdfStates::STATE_ЗАВЕРШЕНО);
                break;
            case self::CLAUSE_STATE_ВСЕ:
                $query->andFilterWhere([
                    $edfTableName . '.state_id' => $this->state_id,
                ]);
                break;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            $edfTableName . '.id' => $this->id,
            $edfTableName . '.created_at' => $this->created_at,
            $edfTableName . '.created_by' => $this->created_by,
            $edfTableName . '.type_id' => $this->type_id,
            $edfTableName . '.parent_id' => $this->parent_id,
            $edfTableName . '.ct_id' => $this->ct_id,
            $edfTableName . '.org_id' => $this->org_id,
            $edfTableName . '.ba_id' => $this->ba_id,
            $edfTableName . '.manager_id' => $this->manager_id,
            $edfTableName . '.cp_id' => $this->cp_id,
            $edfTableName . '.fo_ca_id' => $this->fo_ca_id,
            $edfTableName . '.doc_date' => $this->doc_date,
        ]);

        // возможный отбор по полю "Типовой договор"
        if (!empty($this->searchTypical)) {
            switch ($this->searchTypical) {
                case Edf::FILTER_TYPICAL_ТИПОВЫЕ:
                    $query->andFilterWhere([
                        $edfTableName . '.is_typical_form' => true,
                    ]);
                    break;
                case Edf::FILTER_TYPICAL_КАСТОМНЫЕ:
                    $query->andFilterWhere([
                        $edfTableName . '.is_typical_form' => false,
                    ]);
                    break;
            }
        }
        else {
            $query->andFilterWhere([
                $edfTableName . '.is_typical_form' => $this->is_typical_form,
            ]);
        }

        // возможный отбор по полю "Скан"
        if (!empty($this->searchScan)) {
            switch ($this->searchScan) {
                case Edf::FILTER_SCAN_ЕСТЬ:
                    $query->andFilterWhere([
                        $edfTableName . '.is_received_scan' => true,
                    ]);
                    break;
                case Edf::FILTER_SCAN_НЕТ:
                    $query->andFilterWhere([
                        $edfTableName . '.is_received_scan' => false,
                    ]);
                    break;
            }
        }
        else {
            $query->andFilterWhere([
                $edfTableName . '.is_received_scan' => $this->is_received_scan,
            ]);
        }

        // возможный отбор по полю "Оригинал"
        if (!empty($this->searchOriginal)) {
            switch ($this->searchOriginal) {
                case Edf::FILTER_ORIGINAL_ЕСТЬ:
                    $query->andFilterWhere([
                        $edfTableName . '.is_received_original' => true,
                    ]);
                    break;
                case Edf::FILTER_ORIGINAL_НЕТ:
                    $query->andFilterWhere([
                        $edfTableName . '.is_received_original' => false,
                    ]);
                    break;
            }
        }
        else {
            $query->andFilterWhere([
                $edfTableName . '.is_received_original' => $this->is_received_original,
            ]);
        }

        // возможный отбор только просроченных документов
        if (!empty($this->searchOutdatedOnly)) {
            $query->andWhere(['<=', $edfTableName . '.doc_date_expires', date('Y-m-d', time())])->andWhere($edfTableName . '.state_id <> ' . EdfStates::STATE_ЗАВЕРШЕНО);
        }
        else {
            $query->andFilterWhere([
                $edfTableName . '.doc_date_expires' => $this->doc_date_expires,
            ]);
        }

        $query->andFilterWhere(['like', $edfTableName . '.doc_num', $this->doc_num])
            ->andFilterWhere(['like', $edfTableName . '.basis', $this->basis])
            ->andFilterWhere(['like', $edfTableName . '.req_name_full', $this->req_name_full])
            ->andFilterWhere(['like', $edfTableName . '.req_name_short', $this->req_name_short])
            ->andFilterWhere(['like', $edfTableName . '.req_ogrn', $this->req_ogrn])
            ->andFilterWhere(['like', $edfTableName . '.req_inn', $this->req_inn])
            ->andFilterWhere(['like', $edfTableName . '.req_kpp', $this->req_kpp])
            ->andFilterWhere(['like', $edfTableName . '.req_address_j', $this->req_address_j])
            ->andFilterWhere(['like', $edfTableName . '.req_address_f', $this->req_address_f])
            ->andFilterWhere(['like', $edfTableName . '.req_an', $this->req_an])
            ->andFilterWhere(['like', $edfTableName . '.req_bik', $this->req_bik])
            ->andFilterWhere(['like', $edfTableName . '.req_bn', $this->req_bn])
            ->andFilterWhere(['like', $edfTableName . '.req_ca', $this->req_ca])
            ->andFilterWhere(['like', $edfTableName . '.req_phone', $this->req_phone])
            ->andFilterWhere(['like', $edfTableName . '.req_email', $this->req_email])
            ->andFilterWhere(['like', $edfTableName . '.req_dir_post', $this->req_dir_post])
            ->andFilterWhere(['like', $edfTableName . '.req_dir_name', $this->req_dir_name])
            ->andFilterWhere(['like', $edfTableName . '.req_dir_name_of', $this->req_dir_name_of])
            ->andFilterWhere(['like', $edfTableName . '.req_dir_name_short', $this->req_dir_name_short])
            ->andFilterWhere(['like', $edfTableName . '.req_dir_name_short_of', $this->req_dir_name_short_of])
            ->andFilterWhere(['like', $edfTableName . '.files_full_path', $this->files_full_path])
            ->andFilterWhere(['like', $edfTableName . '.comment', $this->comment]);

        return $dataProvider;
    }
}
