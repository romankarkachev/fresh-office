<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\LicensesRequests;

/**
 * LicensesRequestsSearch represents the model behind the search form about `common\models\LicensesRequests`.
 */
class LicensesRequestsSearch extends LicensesRequests
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'state_id', 'ca_id', 'org_id'], 'integer'],
            [['ca_email', 'ca_name', 'comment'], 'safe'],
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
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = LicensesRequests::find();
        $query->select([
            '*',
            'id' => 'licenses_requests.id',
            'fkkos' => 'tpfkkos.details',
        ]);

        $query->leftJoin('(
            SELECT
                licenses_requests_fkko.lr_id,
                COUNT(licenses_requests_fkko.id) AS count,
                GROUP_CONCAT(CONCAT(fkko.fkko_code, " - ", fkko.fkko_name) ORDER BY fkko.fkko_name SEPARATOR "<br />") AS details
            FROM licenses_requests_fkko
            LEFT JOIN fkko ON fkko.id = licenses_requests_fkko.fkko_id
            GROUP BY licenses_requests_fkko.lr_id
        ) AS tpfkkos', 'licenses_requests.id = tpfkkos.lr_id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => 'licenses-requests',
                'pageSize' => 50,
            ],
            'sort' => [
                'route' => 'licenses-requests',
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'id',
                    'created_at',
                    'created_by',
                    'state_id',
                    'ca_email',
                    'ca_name',
                    'ca_id',
                    'comment',
                    'createdByName' => [
                        'asc' => ['profile.name' => SORT_ASC],
                        'desc' => ['profile.name' => SORT_DESC],
                    ],
                    'fkkos',
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['createdByProfile', 'organization']);

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
            'state_id' => $this->state_id,
            'ca_email' => $this->ca_email,
            'ca_id' => $this->ca_id,
            'org_id' => $this->org_id,
        ]);

        $query->andFilterWhere(['like', 'ca_name', $this->ca_name])
            ->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
