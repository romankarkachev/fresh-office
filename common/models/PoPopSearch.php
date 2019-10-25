<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\PoPop;

/**
 * PoPopSearch represents the model behind the search form of `common\models\PoPop`.
 */
class PoPopSearch extends PoPop
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'po_id', 'ei_id', 'property_id', 'value_id'], 'integer'],
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
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = PoPop::find()->orderBy(PoProperties::tableName() . '.name');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['propertyName' => SORT_DESC],
                'attributes' => [
                    'id',
                    'po_id',
                    'ei_id',
                    'property_id',
                    'value_id',
                    'propertyName' => [
                        'asc' => [PoProperties::tableName() . '.name' => SORT_ASC],
                        'desc' => [PoProperties::tableName() . '.name' => SORT_DESC],
                    ],
                    'valueName' => [
                        'asc' => [PoValues::tableName() . '.name' => SORT_ASC],
                        'desc' => [PoValues::tableName() . '.name' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['po', 'ei', 'property', 'value']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'po_id' => $this->po_id,
            'ei_id' => $this->ei_id,
            'property_id' => $this->property_id,
            'value_id' => $this->value_id,
        ]);

        return $dataProvider;
    }
}
