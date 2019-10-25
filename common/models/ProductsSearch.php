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
     * Поле для поиска по нескольким полям.
     * @var
     */
    public $searchField;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'is_deleted', 'type', 'unit_id', 'hk_id', 'dc_id', 'fkko_id', 'fo_id'], 'integer'],
            [['name', 'src_unit', 'src_uw', 'src_dc', 'src_fkko', 'fkko_date', 'fo_name', 'fo_fkko'], 'safe'],
            // для отбора
            [['searchField'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'searchField' => 'Универсальный поиск',
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
            'created_by' => $this->created_by,
            'is_deleted' => $this->is_deleted,
            'type' => $this->type,
            'unit_id' => $this->unit_id,
            'hk_id' => $this->hk_id,
            'dc_id' => $this->dc_id,
            'fkko_id' => $this->fkko_id,
            'fkko_date' => $this->fkko_date,
            'fo_id' => $this->fo_id,
        ]);

        if (!empty($this->searchField)) {
            $query->andWhere([
                'or',
                ['like', 'name', $this->searchField],
                ['like', 'src_fkko', $this->searchField],
                ['fo_id' => $this->searchField],
                ['id' => $this->searchField],
            ]);
        }
        else {
            $query->andFilterWhere(['like', 'name', $this->name])
                ->andFilterWhere(['like', 'src_unit', $this->src_unit])
                ->andFilterWhere(['like', 'src_uw', $this->src_uw])
                ->andFilterWhere(['like', 'src_dc', $this->src_dc])
                ->andFilterWhere(['like', 'src_fkko', $this->src_fkko])
                ->andFilterWhere(['like', 'fo_name', $this->fo_name])
                ->andFilterWhere(['like', 'fo_fkko', $this->fo_fkko]);
        }

        return $dataProvider;
    }
}
