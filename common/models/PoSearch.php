<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Po;
use yii\helpers\ArrayHelper;

/**
 * PoSearch represents the model behind the search form of `common\models\Po`.
 */
class PoSearch extends Po
{
    /**
     * @var string поле отбора, определяющее начало периода даты оплаты
     */
    public $searchPaymentDateStart;

    /**
     * @var string поле отбора, определяющее окончания периода даты оплаты
     */
    public $searchPaymentDateEnd;

    /**
     * @var float поле отбора, определяющее левую границу диапазона сумм
     */
    public $searchAmountStart;

    /**
     * @var float поле отбора, определяющее правую границу диапазона сумм
     */
    public $searchAmountEnd;

    /**
     * @var integer флаг для управления группами статусов при отборе
     */
    public $searchGroupStates;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'state_id', 'company_id', 'ei_id', 'approved_at', 'paid_at', 'searchGroupStates'], 'integer'],
            [['amount', 'searchAmountStart', 'searchAmountEnd'], 'number'],
            [['comment', 'searchPaymentDateStart', 'searchPaymentDateEnd'], 'safe'],
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
            'searchAmountStart' => 'Сумма от',
            'searchAmountEnd' => 'Сумма до',
            'searchGroupStates' => 'Группы статусов',
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
     * @param $route string маршрут для сортировки и постраничного перехода
     * @return ActiveDataProvider
     */
    public function search($params, $route = 'po')
    {
        $tableName = Po::tableName();
        $query = Po::find();

        if (Yii::$app->user->can('logist') || Yii::$app->user->can('assistant') || Yii::$app->user->can('ecologist') || Yii::$app->user->can('ecologist_head') || Yii::$app->user->can('prod_department_head') || Yii::$app->user->can('tenders_manager')) {
            $query->where([
                $tableName . '.created_by' => Yii::$app->user->id,
            ]);
        }

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
                    'id' => [
                        'asc' => [$tableName . '.id' => SORT_ASC],
                        'desc' => [$tableName . '.id' => SORT_DESC],
                    ],
                    'created_at',
                    'created_by',
                    'state_id',
                    'company_id',
                    'ei_id',
                    'amount',
                    'approved_at',
                    'paid_at',
                    'comment',
                    'createdByProfileName' => [
                        'asc' => ['createdByProfile.name' => SORT_ASC],
                        'desc' => ['createdByProfile.name' => SORT_DESC],
                    ],
                    'stateName' => [
                        'asc' => [PaymentOrdersStates::tableName() . '.name' => SORT_ASC],
                        'desc' => [PaymentOrdersStates::tableName() . '.name' => SORT_DESC],
                    ],
                    'companyName' => [
                        'asc' => [Companies::tableName() . '.name' => SORT_ASC],
                        'desc' => [Companies::tableName() . '.name' => SORT_DESC],
                    ],
                    'eiName' => [
                        'asc' => [PoEi::tableName() . '.name' => SORT_ASC],
                        'desc' => [PoEi::tableName() . '.name' => SORT_DESC],
                    ],
                    'eigName' => [
                        'asc' => [PoEig::tableName() . '.name' => SORT_ASC],
                        'desc' => [PoEig::tableName() . '.name' => SORT_DESC],
                    ],
                    'eiRepHtml' => [
                        'asc' => [PoEig::tableName() . '.name' => SORT_ASC, PoEi::tableName() . '.name' => SORT_ASC],
                        'desc' => [PoEig::tableName() . '.name' => SORT_DESC, PoEi::tableName() . '.name' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['createdByProfile', 'state', 'company', 'ei', 'eiGroup']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // дополним условием отбора за период по дате оплаты
        if (!empty($this->searchPaymentDateStart) || !empty($this->searchPaymentDateEnd)) {
            if (!empty($this->searchPaymentDateStart) && !empty($this->searchPaymentDateEnd)) {
                // если указаны обе даты
                $query->andFilterWhere(['between', Po::tableName() . '.paid_at', strtotime($this->searchPaymentDateStart . ' 00:00:00'), strtotime($this->searchPaymentDateEnd . ' 23:59:59')]);
            } else if (!empty($this->searchPaymentDateStart) && empty($this->searchPaymentDateEnd)) {
                // если указано только начало периода
                $query->andFilterWhere(['>=', Po::tableName() . '.paid_at', strtotime($this->searchPaymentDateStart . ' 00:00:00')]);
            } else if (empty($this->searchPaymentDateStart) && !empty($this->searchPaymentDateEnd)) {
                // если указан только конец периода
                $query->andFilterWhere(['<=', Po::tableName() . '.paid_at', strtotime($this->searchPaymentDateEnd . ' 23:59:59')]);
            };
        }

        // дополним условием отбора по сумме
        if (!empty($this->searchAmountStart) || !empty($this->searchAmountEnd)) {
            if (!empty($this->searchAmountStart) && !empty($this->searchAmountEnd)) {
                // диапазон указан целиком
                $query->andWhere([
                    'between',
                    Po::tableName() . '.`amount`',
                    $this->searchAmountStart,
                    $this->searchAmountEnd
                ]);
            }
            elseif (!empty($this->searchAmountStart) && empty($this->searchAmountEnd)) {
                // указана только левая граница диапазона, берем все суммы больше или равно
                $query->andWhere(Po::tableName() . '.`amount` >= ' . $this->searchAmountStart);
            }
            elseif (empty($this->searchAmountStart) && !empty($this->searchAmountEnd)) {
                // указана только правая граница диапазона, берем все суммы меньше или равно
                $query->andWhere(Po::tableName() . '.`amount` <= ' . $this->searchAmountEnd);
            }
        }

        if (empty($this->searchGroupStates))
            if (Yii::$app->user->can('accountant') || Yii::$app->user->can('accountant_b'))
                $this->searchGroupStates = PaymentOrdersSearch::CLAUSE_STATES_GROUP_ACCOUNTANT_DEFAULT;
            elseif (Yii::$app->user->can('logist') || Yii::$app->user->can('root'))
                $this->searchGroupStates = PaymentOrdersSearch::CLAUSE_STATES_GROUP_ROOT_LOGIST_ALL;

        if ($this->searchGroupStates != null) switch ($this->searchGroupStates) {
            case PaymentOrdersSearch::CLAUSE_STATES_GROUP_ACCOUNTANT_DEFAULT:
                $query->andWhere(['in', Po::tableName() . '.state_id', PaymentOrdersStates::PAYMENT_STATES_SET_ACCOUNTANT_DEFAULT]);
                break;
            case PaymentOrdersSearch::CLAUSE_STATES_GROUP_ACCOUNTANT_PAID:
                $query->andWhere(['in', Po::tableName() . '.state_id', PaymentOrdersStates::PAYMENT_STATES_SET_ACCOUNTANT_PAID]);
                break;
            case PaymentOrdersSearch::CLAUSE_STATES_GROUP_ACCOUNTANT_ALL:
                $query->andWhere(['in', Po::tableName() . '.state_id', PaymentOrdersStates::PAYMENT_STATES_SET_ACCOUNTANT_ALL]);
                break;
            case PaymentOrdersSearch::CLAUSE_STATES_GROUP_ROOT_LOGIST_ALL:
                // нет условия, все записи (без отбора по этому полю)
                break;
            default:
                $query->andWhere([Po::tableName() . '.state_id' => $this->searchGroupStates]);
                break;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            $tableName . '.id' => $this->id,
            $tableName . '.created_at' => $this->created_at,
            $tableName . '.created_by' => $this->created_by,
            'state_id' => $this->state_id,
            'company_id' => $this->company_id,
            'ei_id' => $this->ei_id,
            'amount' => $this->amount,
            'approved_at' => $this->approved_at,
            'paid_at' => $this->paid_at,
        ]);

        $query->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
