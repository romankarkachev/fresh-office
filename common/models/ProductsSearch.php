<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Products;

/**
 * ProductsSearch represents the model behind the search form about `common\models\Products`.
 */
class ProductsSearch extends Products
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'is_deleted', 'author_id', 'type', 'fkko', 'fo_id'], 'integer'],
            [['name', 'unit', 'uw', 'dc', 'fkko_date', 'fo_name', 'fo_fkko'], 'safe'],
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
        $query = Products::find();

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
            'is_deleted' => $this->is_deleted,
            'author_id' => $this->author_id,
            'type' => $this->type,
            'fkko' => $this->fkko,
            'fkko_date' => $this->fkko_date,
            'fo_id' => $this->fo_id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'unit', $this->unit])
            ->andFilterWhere(['like', 'uw', $this->uw])
            ->andFilterWhere(['like', 'dc', $this->dc])
            ->andFilterWhere(['like', 'fo_name', $this->fo_name])
            ->andFilterWhere(['like', 'fo_fkko', $this->fo_fkko]);

        return $dataProvider;
    }
}
