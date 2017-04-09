<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Appeals;

/**
 * AppealsSearch represents the model behind the search form about `common\models\Appeals`.
 */
class AppealsSearch extends Appeals
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'state_id', 'fo_id_company', 'ca_state_id', 'as_id'], 'integer'],
            [['form_company', 'form_username', 'form_region', 'form_phone', 'form_email', 'form_message', 'fo_company_name'], 'safe'],
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
        $query = Appeals::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'route' => 'appeals',
            'defaultOrder' => ['created_at' => SORT_DESC],
            'attributes' => [
                'id',
                'created_at',
                'state_id',
                'form_company',
                'form_username',
                'form_region',
                'form_phone',
                'form_email',
                'form_message',
                'fo_id_company',
                'fo_company_name',
                'fo_id_manager',
                'ca_state_id',
                'as_id',
                'request_referrer',
                'request_user_agent',
                'request_user_ip',
                //'appealStateName',
                //'caStateName',
                'appealSourceName' => [
                    'asc' => ['appeal_sources.name' => SORT_ASC],
                    'desc' => ['appeal_sources.name' => SORT_DESC],
                ],
            ]
        ]);

        $this->load($params);
        $query->joinWith(['as']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'created_at' => $this->created_at,
            'state_id' => $this->state_id,
            'fo_id_company' => $this->fo_id_company,
            'ca_state_id' => $this->ca_state_id,
            'as_id' => $this->as_id,
        ]);

        $query->andFilterWhere(['like', 'form_company', $this->form_company])
            ->andFilterWhere(['like', 'form_username', $this->form_username])
            ->andFilterWhere(['like', 'form_region', $this->form_region])
            ->andFilterWhere(['like', 'form_phone', $this->form_phone])
            ->andFilterWhere(['like', 'form_email', $this->form_email])
            ->andFilterWhere(['like', 'form_message', $this->form_message])
            ->andFilterWhere(['like', 'fo_company_name', $this->fo_company_name]);

        return $dataProvider;
    }
}
