<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\pbxCallsComments;

/**
 * pbxCallsCommentsSearch represents the model behind the search form about `common\models\pbxCallsComments`.
 */
class pbxCallsCommentsSearch extends pbxCallsComments
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'call_id', 'user_id'], 'integer'],
            [['added_timestamp', 'contents'], 'safe'],
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
     * @param $route string URL для сортировки и постраничного перехода
     * @return ActiveDataProvider
     */
    public function search($params, $route='pbx-calls')
    {
        $query = pbxCallsComments::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => $route,
                'pageSize' => 10,
            ],
            'sort' => [
                'route' => $route,
                'defaultOrder' => ['added_timestamp' => SORT_DESC],
                'attributes' => [
                    'id',
                    'call_id',
                    'added_timestamp',
                    'contents',
                    'user_id',
                ],
            ],
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
            'call_id' => $this->call_id,
            'added_timestamp' => $this->added_timestamp,
            'user_id' => $this->user_id,
        ]);

        $query->andFilterWhere(['like', 'contents', $this->contents]);

        return $dataProvider;
    }
}
