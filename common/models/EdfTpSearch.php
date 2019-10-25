<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * EdfTpSearch represents the model behind the search form about `common\models\EdfTp`.
 */
class EdfTpSearch extends EdfTp
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'ed_id', 'fkko_id', 'unit_id', 'hk_id'], 'integer'],
            [['fkko_name'], 'safe'],
            [['measure', 'price'], 'number'],
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
        $query = EdfTp::find();
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
            'ed_id' => $this->ed_id,
            'fkko_id' => $this->fkko_id,
            'unit_id' => $this->unit_id,
            'measure' => $this->measure,
            'hk_id' => $this->hk_id,
            'price' => $this->price,
        ]);

        $query->andFilterWhere(['like', 'fkko_name', $this->fkko_name]);

        return $dataProvider;
    }
}
