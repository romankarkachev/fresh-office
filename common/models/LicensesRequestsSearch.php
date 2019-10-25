<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\LicensesRequests;
use yii\helpers\ArrayHelper;

/**
 * LicensesRequestsSearch represents the model behind the search form about `common\models\LicensesRequests`.
 */
class LicensesRequestsSearch extends LicensesRequests
{
    /**
     * Поле для отбора по отходу (fkko_name).
     * @var integer
     */
    public $searchFkkoName;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'state_id', 'ca_id', 'org_id'], 'integer'],
            [['ca_email', 'ca_name', 'comment', 'searchFkkoName'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'searchFkkoName' => 'Отход',
        ]);
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
                    'organizationName' => [
                        'asc' => ['organizations.name' => SORT_ASC],
                        'desc' => ['organizations.name' => SORT_DESC],
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

        if (!empty($this->searchFkkoName)) {
            // отбор по наименованию отхода, дополним условием основной запрос
            $query->andWhere(['in', 'licenses_requests.id', LicensesRequestsFkko::find()
                ->select(['id' => 'lr_id'])
                ->leftJoin(Fkko::tableName(), Fkko::tableName() . '.id = ' . LicensesRequestsFkko::tableName() . '.fkko_id')
                ->andFilterWhere([
                    'or',
                    ['like', 'fkko_code', $this->searchFkkoName],
                    ['like', 'fkko_name', $this->searchFkkoName],
                ])
                ->asArray()
                ->column()
            ]);
        }
        else {
            $query->andFilterWhere([
                'id' => $this->id,
            ]);
        }

        if (Yii::$app->user->can('tenders_manager')) {
            // для специалистов отдела тендеров отбор только по собственным запросам лицензий
            $query->andWhere([
                'created_by' => Yii::$app->user->id,
            ]);
        }
        else {
            $query->andFilterWhere([
                'created_by' => $this->created_by,
            ]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'created_at' => $this->created_at,
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
