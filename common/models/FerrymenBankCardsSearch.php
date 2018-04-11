<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\FerrymenBankCards;

/**
 * FerrymenBankCardsSearch represents the model behind the search form about `common\models\FerrymenBankCards`.
 */
class FerrymenBankCardsSearch extends FerrymenBankCards
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'ferryman_id'], 'integer'],
            [['cardholder', 'number', 'bank'], 'safe'],
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
     * @param $route string URL для постраничного перехода и сортировки
     * @return ActiveDataProvider
     */
    public function search($params, $route='ferrymen-bank-cards')
    {
        $query = FerrymenBankCards::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['route' => $route],
            'sort' => ['route' => $route],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // если запрос выполняет перевозчик, то ограничим выборку только по нему
        if (Yii::$app->user->can('ferryman')) {
            $ferryman = Ferrymen::findOne(['user_id' => Yii::$app->user->id]);
            // если связанный перевозчик не будет обнаружен, то вернем пустую выборку
            if ($ferryman == null) {$query->where('1 <> 1'); return $dataProvider;}
            $query->andWhere(['ferryman_id' => $ferryman->id]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'ferryman_id' => $this->ferryman_id,
        ]);

        $query->andFilterWhere(['like', 'cardholder', $this->cardholder])
            ->andFilterWhere(['like', 'number', $this->number])
            ->andFilterWhere(['like', 'bank', $this->bank]);

        return $dataProvider;
    }
}
