<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\EcoMc;
use yii\db\Expression;

/**
 * EcoMcSearch represents the model behind the search form of `common\models\EcoMc`.
 */
class EcoMcSearch extends EcoMc
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'fo_ca_id', 'manager_id'], 'integer'],
            [['amount'], 'number'],
            [['date_start', 'date_finish', 'comment'], 'safe'],
        ];
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
     * @param array $params
     * @param $route string URL для перехода в список записей
     * @return ActiveDataProvider
     */
    public function search($params, $route = 'eco-contracts')
    {
        $sqReports = EcoMcTp::find()
            ->select(new Expression('GROUP_CONCAT(CONCAT_WS(
                "' . mb_chr(0x2219, 'UTF-8') . '",
                ' .  EcoMcTp::tableName() . '.id,
                ' .  EcoMcTp::tableName() . '.report_id,
                ' .  EcoReportsKinds::tableName() . '.name,
                ' . EcoMcTp::tableName() . '.date_deadline,
                ' . EcoMcTp::tableName() . '.date_fact
            ) SEPARATOR "\r\n")'))
            ->leftJoin(EcoReportsKinds::tableName(), EcoReportsKinds::tableName() . '.id = ' . EcoMcTp::tableName() . '.report_id')
            ->where(EcoMcTp::tableName() . '.mc_id = ' . EcoMc::tableName() . '.id')
            ->groupBy(EcoMcTp::tableName() . '.mc_id');

        $query = EcoMc::find()->select([
            EcoMc::tableName() . '.*',
            'id' => EcoMc::tableName() . '.id',
            'reports' => $sqReports,
        ]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => $route,
            ],
            'sort' => [
                'route' => $route,
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'id' => [
                        'asc' => [EcoMc::tableName() . '.id' => SORT_ASC],
                        'desc' => [EcoMc::tableName() . '.id' => SORT_DESC],
                    ],
                    'created_at',
                    'created_by',
                    'fo_ca_id',
                    'manager_id',
                    'amount',
                    'date_start',
                    'date_finish',
                    'comment',
                    'createdByProfileName' => [
                        'asc' => ['createdByProfile.name' => SORT_ASC],
                        'desc' => ['createdByProfile.name' => SORT_DESC],
                    ],
                    'managerProfileName' => [
                        'asc' => ['managerProfile.name' => SORT_ASC],
                        'desc' => ['managerProfile.name' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['createdByProfile', 'managerProfile']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'fo_ca_id' => $this->fo_ca_id,
            'manager_id' => $this->manager_id,
            'amount' => $this->amount,
            'date_start' => $this->date_start,
            'date_finish' => $this->date_finish,
        ]);

        $query->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
