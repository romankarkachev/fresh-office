<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\FerrymenBankDetails;

/**
 * FerrymenBankDetailsSearch represents the model behind the search form about `common\models\FerrymenBankDetails`.
 */
class FerrymenBankDetailsSearch extends FerrymenBankDetails
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'ferryman_id'], 'integer'],
            [['name_full', 'inn', 'kpp', 'ogrn', 'bank_an', 'bank_bik', 'bank_name', 'bank_ca', 'comment'], 'safe'],
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
    public function search($params, $route='ferrymen-bank-details')
    {
        $query = FerrymenBankDetails::find();
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

        $query->andFilterWhere(['like', 'name_full', $this->name_full])
            ->andFilterWhere(['like', 'inn', $this->inn])
            ->andFilterWhere(['like', 'kpp', $this->kpp])
            ->andFilterWhere(['like', 'ogrn', $this->ogrn])
            ->andFilterWhere(['like', 'bank_an', $this->bank_an])
            ->andFilterWhere(['like', 'bank_bik', $this->bank_bik])
            ->andFilterWhere(['like', 'bank_name', $this->bank_name])
            ->andFilterWhere(['like', 'bank_ca', $this->bank_ca])
            ->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
