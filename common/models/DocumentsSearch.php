<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Documents;

/**
 * DocumentsSearch represents the model behind the search form about `common\models\Documents`.
 */
class DocumentsSearch extends Documents
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'author_id', 'fo_project', 'fo_customer', 'fo_contract'], 'integer'],
            [['doc_date', 'comment'], 'safe'],
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
        $query = Documents::find()->select([
            '*',
            'id' => Documents::tableName() . '.id',
            'created_at' => Documents::tableName() . '.created_at',
            'doc_num' => Documents::tableName() . '.doc_num',
            'doc_date' => Documents::tableName() . '.doc_date',
            'org_id' => Documents::tableName() . '.org_id',
            'tpCount' => DocumentsTp::find()->select('COUNT(*)')->where(DocumentsTp::tableName() . '.doc_id = ' . Documents::tableName() . '.id')->groupBy('doc_id'),
        ]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => 'documents',
                'pageSize' => 50,
            ],
            'sort' => [
                'route' => 'documents',
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'id',
                    'created_at' => [
                        'asc' => [Documents::tableName() . '.created_at' => SORT_ASC],
                        'desc' => [Documents::tableName() . '.created_at' => SORT_DESC],
                    ],
                    'author_id',
                    'doc_num',
                    'doc_date',
                    'act_date',
                    'org_id',
                    'fo_project',
                    'fo_customer',
                    'fo_contract',
                    'ed_id',
                    'comment',
                    'tpCount',
                    'createdByProfileName' => [
                        'asc' => ['profile.name' => SORT_ASC],
                        'desc' => ['profile.name' => SORT_DESC],
                    ],
                    'organizationName' => [
                        'asc' => ['organizations.name' => SORT_ASC],
                        'desc' => ['organizations.name' => SORT_DESC],
                    ],
                    'edRep' => [
                        'asc' => ['edf.doc_date' => SORT_ASC],
                        'desc' => ['edf.doc_date' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['createdByProfile', 'organization', 'ed']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'created_at' => $this->created_at,
            'author_id' => $this->author_id,
            'doc_date' => $this->doc_date,
            'fo_project' => $this->fo_project,
            'fo_customer' => $this->fo_customer,
            'fo_contract' => $this->fo_contract,
        ]);

        $query->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
