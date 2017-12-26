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

    const CLAUSE_STATES_GROUP_ROOT_LOGIST_ALL = 999911;

    /**
     * @var integer флаг для управления группами статусов при отборе
     */
    public $searchGroupStates;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'state_id', 'ferryman_id', 'pd_type', 'pd_id', 'searchGroupStates'], 'integer'],
            [['projects', 'payment_date', 'comment'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'searchGroupStates' => 'Группы статусов',
        ]);
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
        ], PaymentOrdersStates::find()->asArray()->all());
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
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = PaymentOrders::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => 'payment-orders',
            ],
            'sort' => [
                'route' => 'payment-orders',
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'id',
                    'created_at',
                    'created_by',
                    'state_id',
                    'ferryman_id',
                    'projects',
                    'amount',
                    'pd_type',
                    'pd_id',
                    'payment_date',
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
                ]
            ]
        ]);

        $this->load($params);
        $query->joinWith(['createdByProfile', 'state', 'ferryman']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if ($this->searchGroupStates == null)
            if (Yii::$app->user->can('accountant'))
                $this->searchGroupStates = self::CLAUSE_STATES_GROUP_ACCOUNTANT_DEFAULT;
            elseif (Yii::$app->user->can('logist') || Yii::$app->user->can('root'))
                $this->searchGroupStates = self::CLAUSE_STATES_GROUP_ROOT_LOGIST_ALL;

        if ($this->searchGroupStates != null) switch ($this->searchGroupStates) {
            case self::CLAUSE_STATES_GROUP_ACCOUNTANT_DEFAULT:
                $query->andWhere(['in', 'payment_orders.state_id', PaymentOrdersStates::PAYMENT_STATES_SET_ACCOUNTANT_DEFAULT]);
                break;
            case self::CLAUSE_STATES_GROUP_ACCOUNTANT_PAID:
                $query->andWhere(['in', 'payment_orders.state_id', PaymentOrdersStates::PAYMENT_STATES_SET_ACCOUNTANT_PAID]);
                break;
            case self::CLAUSE_STATES_GROUP_ACCOUNTANT_ALL:
                $query->andWhere(['in', 'payment_orders.state_id', PaymentOrdersStates::PAYMENT_STATES_SET_ACCOUNTANT_ALL]);
                break;
            case self::CLAUSE_STATES_GROUP_ROOT_LOGIST_ALL:
                // нет условия, все записи (без отбора по этому полю)
                break;
            default:
                $query->andWhere(['payment_orders.state_id' => $this->searchGroupStates]);
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

        return $dataProvider;
    }
}
