<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\controllers\PoEiController;

/**
 * PoEiSearch represents the model behind the search form of `common\models\PoEi`.
 */
class PoEiSearch extends PoEi
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'group_id'], 'integer'],
            [['name'], 'safe'],
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
        $query = PoEi::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => PoEiController::ROOT_URL_FOR_SORT_PAGING,
            ],
            'sort' => [
                'route' => PoEiController::ROOT_URL_FOR_SORT_PAGING,
                'defaultOrder' => ['group_id' => SORT_ASC],
                'attributes' => [
                    'id',
                    'group_id',
                    'name',
                    'groupName' => [
                        'asc' => ['po_eig.name' => SORT_ASC],
                        'desc' => ['po_eig.name' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['group']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'group_id' => $this->group_id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
