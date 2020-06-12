<?php

namespace common\models;

use backend\controllers\AdvanceHoldersController;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\FinanceAdvanceHolders;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * FinanceAdvanceHoldersSearch represents the model behind the search form of `common\models\FinanceAdvanceHolders`.
 */
class FinanceAdvanceHoldersSearch extends FinanceAdvanceHolders
{
    /**
     * @var float поле отбора, определяющее левую границу диапазона сумм
     */
    public $searchBalanceStart;

    /**
     * @var float поле отбора, определяющее правую границу диапазона сумм
     */
    public $searchBalanceEnd;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id'], 'integer'],
            [['balance', 'searchBalanceStart', 'searchBalanceEnd'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'searchBalanceStart' => 'Сумма от',
            'searchBalanceEnd' => 'Сумма до',
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
    public function search($params, $route = AdvanceHoldersController::ROOT_URL_FOR_SORT_PAGING)
    {
        $tableName = FinanceAdvanceHolders::tableName();
        $query = FinanceAdvanceHolders::find()->select([
            $tableName . '.*',
            'id' => $tableName . '.`id`',
            'lastTransaction' => FinanceTransactions::find()->select(new Expression('CONCAT(`operation`, "' . mb_chr(0x2219, 'UTF-8') . '", `amount`, "' . mb_chr(0x2219, 'UTF-8') . '", `created_at`)'))->where([
                FinanceTransactions::tableName() . '.`user_id`' => new Expression($tableName . '.`user_id`')
            ])->orderBy(['created_at' => SORT_DESC])->limit(1),
        ]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => $route,
                'pageSize' => 50,
            ],
        ]);

        $this->load($params);
        $query->joinWith(['userProfile']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // дополним условием отбора по сумме
        if (!empty($this->searchBalanceStart) || !empty($this->searchBalanceEnd)) {
            if (!empty($this->searchBalanceStart) && !empty($this->searchBalanceEnd)) {
                // диапазон указан целиком
                $query->andWhere([
                    'between',
                    $tableName . '.`balance`',
                    $this->searchBalanceStart,
                    $this->searchBalanceEnd
                ]);
            }
            elseif (!empty($this->searchBalanceStart) && empty($this->searchBalanceEnd)) {
                // указана только левая граница диапазона, берем все суммы больше или равно
                $query->andWhere($tableName . '.`balance` >= ' . $this->searchBalanceStart);
            }
            elseif (empty($this->searchBalanceStart) && !empty($this->searchBalanceEnd)) {
                // указана только правая граница диапазона, берем все суммы меньше или равно
                $query->andWhere($tableName . '.`balance` <= ' . $this->searchBalanceEnd);
            }
        }

        // grid filtering conditions
        $query->andFilterWhere([
            $tableName . '.id' => $this->id,
            $tableName . '.user_id' => $this->user_id,
            'balance' => $this->balance,
        ]);

        return $dataProvider;
    }
}
