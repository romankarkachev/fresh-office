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
            [['id', 'created_at', 'fo_id_company', 'ca_state_id'], 'integer'],
            [['form_username', 'form_region', 'form_phone', 'form_email', 'form_message'], 'safe'],
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

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'created_at' => $this->created_at,
            'fo_id_company' => $this->fo_id_company,
            'ca_state_id' => $this->ca_state_id,
        ]);

        $query->andFilterWhere(['like', 'form_username', $this->form_username])
            ->andFilterWhere(['like', 'form_region', $this->form_region])
            ->andFilterWhere(['like', 'form_phone', $this->form_phone])
            ->andFilterWhere(['like', 'form_email', $this->form_email])
            ->andFilterWhere(['like', 'form_message', $this->form_message]);

        return $dataProvider;
    }
}
