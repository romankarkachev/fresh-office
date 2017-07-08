<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Ferrymen;

/**
 * FerrymenSearch represents the model behind the search form about `common\models\Ferrymen`.
 */
class FerrymenSearch extends Ferrymen
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'ft_id', 'pc_id'], 'integer'],
            [['name', 'phone', 'email', 'contact_person'], 'safe'],
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
        $query = Ferrymen::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => 'ferrymen',
            ],
            'sort' => [
                'route' => 'ferrymen',
                'defaultOrder' => ['name' => SORT_DESC],
                'attributes' => [
                    'id',
                    'name',
                    'ft_id',
                    'pc_id',
                    'phone',
                    'email',
                    'contact_person',
                    'ftName' => [
                        'asc' => ['ferrymen_types.name' => SORT_ASC],
                        'desc' => ['ferrymen_types.name' => SORT_DESC],
                    ],
                    'pcName' => [
                        'asc' => ['payment_conditions.name' => SORT_ASC],
                        'desc' => ['payment_conditions.name' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['ft', 'pc']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'ft_id' => $this->ft_id,
            'pc_id' => $this->pc_id,
        ]);

        $query->andFilterWhere(['like', 'ferrymen.name', $this->name])
            ->andFilterWhere(['like', 'ferrymen.phone', $this->phone])
            ->andFilterWhere(['like', 'ferrymen.email', $this->email])
            ->andFilterWhere(['like', 'contact_person', $this->contact_person]);

        return $dataProvider;
    }
}
