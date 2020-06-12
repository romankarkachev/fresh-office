<?php

namespace common\models;

use backend\controllers\AdvanceHoldersController;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\FinanceTransactions;
use yii\helpers\ArrayHelper;

/**
 * FinanceTransactionsSearch represents the model behind the search form of `common\models\FinanceTransactions`.
 */
class FinanceTransactionsSearch extends FinanceTransactions
{
    /**
     * @var string дата начала периода для отбора
     */
    public $searchPeriodStart;

    /**
     * @var string дата окончания периода для отбора
     */
    public $searchPeriodEnd;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'user_id', 'operation', 'src_id'], 'integer'],
            [['amount'], 'number'],
            [['comment', 'searchPeriodStart', 'searchPeriodEnd'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'searchPeriodStart' => 'Начало периода',
            'searchPeriodEnd' => 'Конец периода',
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
     * @param string $route
     * @return ActiveDataProvider
     */
    public function search($params, $route = AdvanceHoldersController::ROOT_URL_FOR_SORT_PAGING . '/' . AdvanceHoldersController::URL_TRANSACTIONS)
    {
        $tableName = FinanceTransactions::tableName();
        $query = FinanceTransactions::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => $route,
                'pageSize' => 100,
            ],
            'sort' => [
                'route' => $route,
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'id',
                    'created_at',
                    'created_by',
                    'user_id',
                    'operation',
                    'amount',
                    'src_id',
                    'comment',
                    'createdByProfileName' => [
                        'asc' => ['createdByProfile.name' => SORT_ASC],
                        'desc' => ['createdByProfile.name' => SORT_DESC],
                    ],
                    'userProfileName' => [
                        'asc' => ['userProfile.name' => SORT_ASC],
                        'desc' => ['userProfile.name' => SORT_DESC],
                    ],
                    'operationName' => [
                        'asc' => ['operation' => SORT_ASC],
                        'desc' => ['operation' => SORT_DESC],
                    ],
                    'sourceName' => [
                        'asc' => ['src_id' => SORT_ASC],
                        'desc' => ['src_id' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['createdByProfile', 'userProfile']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // возможный отбор за период
        if (!empty($this->searchPeriodStart) || !empty($this->searchPeriodEnd)) {
            if (!empty($this->searchPeriodStart) && !empty($this->searchPeriodEnd)) {
                // если указаны обе даты
                $query->andFilterWhere(['between', $tableName . '.created_at', strtotime($this->searchPeriodStart . ' 00:00:00'), strtotime($this->searchPeriodEnd . ' 23:59:59')]);
            }
            elseif (!empty($this->searchPeriodStart) && empty($this->searchPeriodEnd)) {
                // если указано только начало периода
                $query->andFilterWhere(['>=', $tableName . '.created_at', strtotime($this->searchPeriodStart . ' 00:00:00')]);
            }
            elseif (empty($this->searchPeriodStart) && !empty($this->searchPeriodEnd)) {
                // если указан только конец периода
                $query->andFilterWhere(['<=', $tableName . '.created_at', strtotime($this->searchPeriodEnd . ' 23:59:59')]);
            }
        }
        else {
            $query->andFilterWhere([
                $tableName . '.created_at' => $this->created_at,
            ]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            $tableName . '.id' => $this->id,
            $tableName . '.created_by' => $this->created_by,
            $tableName . '.`user_id`' => $this->user_id,
            $tableName . '.operation' => $this->operation,
            $tableName . '.amount' => $this->amount,
            $tableName . '.src_id' => $this->src_id,
        ]);

        $query->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
