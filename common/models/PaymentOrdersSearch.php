<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\PaymentOrders;
use yii\helpers\ArrayHelper;

/**
 * PaymentOrdersSearch represents the model behind the search form about `common\models\PaymentOrders`.
 */
class PaymentOrdersSearch extends PaymentOrders
{
    /**
     * Группы статусов
     */
    const CLAUSE_STATES_GROUP_ACCOUNTANT_DEFAULT = 999901;
    const CLAUSE_STATES_GROUP_ACCOUNTANT_PAID = 999902;
    const CLAUSE_STATES_GROUP_ACCOUNTANT_ALL = 999903;
    const CLAUSE_STATES_GROUP_FERRYMAN = 999904;

    const CLAUSE_STATES_GROUP_ROOT_LOGIST_ALL = 999911;
    const CLAUSE_STATES_GROUP_ADVANCE_REPORTS = 999922;

    /**
     * @var string поле отбора, определяющее начало периода даты оплаты
     */
    public $searchPaymentDateStart;

    /**
     * @var string поле отбора, определяющее окончания периода даты оплаты
     */
    public $searchPaymentDateEnd;

    /**
     * @var integer флаг для управления группами статусов при отборе
     */
    public $searchGroupStates;

    /**
     * @var integer поле для отбора по скану акта
     */
    public $searchCcp;

    /**
     * @var integer поле для отбора по оригиналу акта
     */
    public $searchOrp;

    /**
     * Количество записей на странице.
     * По-умолчанию - false.
     * @var integer
     */
    public $searchPerPage;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'state_id', 'ferryman_id', 'pd_type', 'pd_id', 'searchGroupStates', 'searchCcp', 'searchOrp', 'searchPerPage'], 'integer'],
            [['projects', 'payment_date', 'comment', 'searchPaymentDateStart', 'searchPaymentDateEnd'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'searchPaymentDateStart' => 'Начало периода',
            'searchPaymentDateEnd' => 'Конец периода',
            'searchGroupStates' => 'Группы статусов',
            'searchCcp' => 'Скан акта',
            'searchOrp' => 'Оригинал акта',
            'searchPerPage' => 'Записей', // на странице
        ]);
    }

    /**
     * Возвращает массив возможных значения для поля отбора по пометке наличию скана акта выполненных работ.
     * @return array
     */
    public static function fetchIsCcpValues()
    {
        return [
            [
                'id' => PoSearch::IS_DELETED_VALUE_NA,
                'name' => 'Не важно',
            ],
            [
                'id' => PoSearch::IS_DELETED_VALUE_TRUE,
                'name' => 'Скан есть',
            ],
            [
                'id' => PoSearch::IS_DELETED_VALUE_FALSE,
                'name' => 'Скана нет',
            ],
        ];
    }

    /**
     * Возвращает массив возможных значения для поля отбора по пометке наличию оригинала акта выполненных работ.
     * @return array
     */
    public static function fetchIsOrpValues()
    {
        return [
            [
                'id' => PoSearch::IS_DELETED_VALUE_NA,
                'name' => 'Не важно',
            ],
            [
                'id' => PoSearch::IS_DELETED_VALUE_TRUE,
                'name' => 'Оригинал есть',
            ],
            [
                'id' => PoSearch::IS_DELETED_VALUE_FALSE,
                'name' => 'Оригинала нет',
            ],
        ];
    }

    /**
     * Возвращает массив с идентификаторами статусов по группам для пользователей с ролью "Бухгалтер".
     * @return array
     */
    public static function fetchGroupStatesForAccountant()
    {
        return [
            [
                'id' => self::CLAUSE_STATES_GROUP_ACCOUNTANT_DEFAULT,
                'name' => 'По умолчанию',
                'hint' => 'Только ордеры к оплате',
            ],
            [
                'id' => self::CLAUSE_STATES_GROUP_ACCOUNTANT_PAID,
                'name' => 'Оплаченные',
                'hint' => 'Только оплаченные ордеры',
            ],
            [
                'id' => self::CLAUSE_STATES_GROUP_ACCOUNTANT_ALL,
                'name' => 'Все',
                'hint' => 'Все: к оплате и оплаченные',
            ],
        ];
    }

    /**
     * Возвращает массив с идентификаторами статусов по группам для пользователей с рольями "Логист" и "Полные права".
     * @return array
     */
    public static function fetchRegularGroupStates()
    {
        return ArrayHelper::merge([
            [
                'id' => self::CLAUSE_STATES_GROUP_ROOT_LOGIST_ALL,
                'name' => 'Все',
            ],
        ], PaymentOrdersStates::find()->where(['not in', 'id', PaymentOrdersStates::PAYMENT_STATES_SET_АВАНСОВЫЕ_ОТЧЕТЫ])->asArray()->all());
    }

    /**
     * Делает выборку значений для поля отбора по скану акта и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfCcpForSelect2()
    {
        return ArrayHelper::map(self::fetchIsCcpValues(), 'id', 'name');
    }

    /**
     * Делает выборку значений для поля отбора по оригиналу акта и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfOrpForSelect2()
    {
        return ArrayHelper::map(self::fetchIsOrpValues(), 'id', 'name');
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
     *
     * @param array $params
     * @param $route string маршрут для сортировки и постраничного перехода
     * @param $calculateTotalAmount bool возвратить массив или один только ActiveDataProvider
     * @return array|ActiveDataProvider
     */
    public function search($params, $route = 'payment-orders', $calculateTotalAmount = null)
    {
        $tableName = self::tableName();
        $query = PaymentOrders::find()->select([
            '{{payment_orders}}.*',
            'paymentOrdersFilesCount' => 'COUNT({{payment_orders_files}}.id)',
        ]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => $route,
                'pageSize' => 50,
            ],
            'sort' => [
                'route' => $route,
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'id',
                    'created_at',
                    'created_by',
                    'state_id',
                    'ferryman_id',
                    'projects',
                    'cas',
                    'vds',
                    'amount',
                    'pd_type',
                    'pd_id',
                    'payment_date',
                    'emf_sent_at',
                    'approved_at',
                    'ccp_at',
                    'or_at',
                    'imt_num',
                    'imt_state',
                    'comment',
                    'createdByProfileName' => [
                        'asc' => ['profile.name' => SORT_ASC],
                        'desc' => ['profile.name' => SORT_DESC],
                    ],
                    'stateName' => [
                        'asc' => ['payment_orders_states.name' => SORT_ASC],
                        'desc' => ['payment_orders_states.name' => SORT_DESC],
                    ],
                    'ferrymanName' => [
                        'asc' => ['ferrymen.name' => SORT_ASC],
                        'desc' => ['ferrymen.name' => SORT_DESC],
                    ],
                ],
            ]
        ]);

        $this->load($params);
        $query->joinWith(['createdByProfile', 'state', 'ferryman', 'paymentOrdersFiles'])->groupBy('{{payment_orders}}.id');

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // значение по умолчанию для поля скана акта
        if (empty($this->searchCcp)) {
            $this->searchCcp = PoSearch::IS_DELETED_VALUE_NA;
        }

        // значение по умолчанию для поля оригинала акта
        if (empty($this->searchOrp)) {
            $this->searchOrp = PoSearch::IS_DELETED_VALUE_NA;
        }

        // дополним текст запроса условием отбора по полю скана акта
        if (!empty($this->searchCcp)) {
            switch ($this->searchCcp) {
                case PoSearch::IS_DELETED_VALUE_TRUE:
                    // только есть скан акта
                    $condition = ['is not ', $tableName . '.`ccp_at`', null];
                    break;
                case PoSearch::IS_DELETED_VALUE_FALSE:
                    // только нет скана акта
                    $condition = ['is', $tableName . '.`ccp_at`', null];
                    break;
            }
            if (!empty($condition)) {
                $query->andWhere($condition);
            }
            unset($condition);
        }
        else {
            $query->andFilterWhere([
                $tableName . '.`ccp_at`' => $this->ccp_at,
            ]);
        }

        // дополним текст запроса условием отбора по полю оригинала акта
        if (!empty($this->searchOrp)) {
            switch ($this->searchOrp) {
                case PoSearch::IS_DELETED_VALUE_TRUE:
                    // только есть оригинал акта
                    $condition = ['is not ', $tableName . '.`or_at`', null];
                    break;
                case PoSearch::IS_DELETED_VALUE_FALSE:
                    // только нет оригинала акта
                    $condition = ['is', $tableName . '.`or_at`', null];
                    break;
            }
            if (!empty($condition)) {
                $query->andWhere($condition);
            }
            unset($condition);
        }
        else {
            $query->andFilterWhere([
                $tableName . '.`or_at`' => $this->or_at,
            ]);
        }

        // дополним условием отбора за период по дате оплаты
        /*
        if (empty($this->searchPaymentDateStart) && empty($this->searchPaymentDateEnd)) {
            // если отбор по дате вообще не выполняется, то принудительно показываем только за текущий год
            $this->searchPaymentDateStart = date('Y') . '-01-01';
            $query->andFilterWhere(['>=', $tableName . '.payment_date', $this->searchPaymentDateStart . ' 00:00:00']);
        }
        else*/if (!empty($this->searchPaymentDateStart) || !empty($this->searchPaymentDateEnd)) {
            if (!empty($this->searchPaymentDateStart) && !empty($this->searchPaymentDateEnd)) {
                // если указаны обе даты
                $query->andFilterWhere(['between', $tableName . '.payment_date', $this->searchPaymentDateStart . ' 00:00:00', $this->searchPaymentDateEnd . ' 23:59:59']);
            }
            elseif (!empty($this->searchPaymentDateStart) && empty($this->searchPaymentDateEnd)) {
                // если указано только начало периода
                $query->andFilterWhere(['>=', $tableName . '.payment_date', $this->searchPaymentDateStart . ' 00:00:00']);
            }
            elseif (empty($this->searchPaymentDateStart) && !empty($this->searchPaymentDateEnd)) {
                // если указан только конец периода
                $query->andFilterWhere(['<=', $tableName . '.payment_date', $this->searchPaymentDateEnd . ' 23:59:59']);
            }
        }

        if ($this->searchGroupStates == null) {
            if (Yii::$app->user->can('accountant') || Yii::$app->user->can('accountant_b'))
                $this->searchGroupStates = self::CLAUSE_STATES_GROUP_ACCOUNTANT_DEFAULT;
            elseif (Yii::$app->user->can('logist') || Yii::$app->user->can('root')) {
                $this->searchGroupStates = self::CLAUSE_STATES_GROUP_ROOT_LOGIST_ALL;
            }
            elseif (Yii::$app->user->can(AuthItem::ROLE_FERRYMAN)) {
                $this->searchGroupStates = self::CLAUSE_STATES_GROUP_FERRYMAN;
            }
        }

        if ($this->searchGroupStates != null) switch ($this->searchGroupStates) {
            case self::CLAUSE_STATES_GROUP_ACCOUNTANT_DEFAULT:
                $states = PaymentOrdersStates::PAYMENT_STATES_SET_ACCOUNTANT_DEFAULT;
                if (Yii::$app->user->can('accountant')) {
                    $states = ArrayHelper::merge($states, [PaymentOrdersStates::PAYMENT_STATE_ЧЕРНОВИК]);
                }
                $query->andWhere(['in', $tableName . '.state_id', $states]);
                unset($states);

                break;
            case self::CLAUSE_STATES_GROUP_ACCOUNTANT_PAID:
                $query->andWhere(['in', $tableName . '.state_id', PaymentOrdersStates::PAYMENT_STATES_SET_ACCOUNTANT_PAID]);
                break;
            case self::CLAUSE_STATES_GROUP_ACCOUNTANT_ALL:
                $states = PaymentOrdersStates::PAYMENT_STATES_SET_ACCOUNTANT_ALL;
                if (Yii::$app->user->can('accountant')) {
                    $states = ArrayHelper::merge($states, [PaymentOrdersStates::PAYMENT_STATE_ЧЕРНОВИК]);
                }
                $query->andWhere(['in', $tableName . '.state_id', $states]);
                unset($states);

                break;
            case self::CLAUSE_STATES_GROUP_ROOT_LOGIST_ALL:
                // нет условия, все записи (без отбора по этому полю)
                break;
            case self::CLAUSE_STATES_GROUP_FERRYMAN:
                // набор статусов для просмотра платежных ордеров из личного кабинета перевозичка
                $query->andWhere([$tableName . '.state_id' => [PaymentOrdersStates::PAYMENT_STATE_ОПЛАЧЕН, PaymentOrdersStates::PAYMENT_STATE_УТВЕРЖДЕН]]);
                break;
            default:
                $query->andWhere([$tableName . '.state_id' => $this->searchGroupStates]);
                break;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'payment_orders.id' => $this->id,
            'payment_orders.created_at' => $this->created_at,
            'payment_orders.created_by' => $this->created_by,
            //'payment_orders.state_id' => $this->state_id,
            'ferryman_id' => $this->ferryman_id,
            'pd_type' => $this->pd_type,
            'pd_id' => $this->pd_id,
            'payment_date' => $this->payment_date,
        ]);

        $query->andFilterWhere(['like', 'projects', $this->projects])
            ->andFilterWhere(['like', 'payment_orders.comment', $this->comment]);

        $totalAmount = $query->sum('amount');
        if ($calculateTotalAmount === true) {
            return [
                $dataProvider,
                $totalAmount,
            ];
        }
        else {
            return $dataProvider;
        }
    }
}
