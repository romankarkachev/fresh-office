<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ResponsibleForProduction;
use yii\db\Expression;

/**
 * ResponsibleForProductionSearch represents the model behind the search form about `common\models\ResponsibleForProduction`.
 */
class ResponsibleForProductionSearch extends ResponsibleForProduction
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'type'], 'integer'],
            [['receiver'], 'safe'],
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
        $query = ResponsibleForProduction::find();
        $query->select([
            '*',
            'typeName' => new Expression('CASE type WHEN 1 THEN "Всегда" ELSE "При несовпадении" END'),
        ]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => 'responsible-for-production',
            ],
            'sort' => [
                'route' => 'responsible-for-production',
                'attributes' => [
                    'id',
                    'type',
                    'receiver',
                    'typeName',
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
            'type' => $this->type,
        ]);

        $query->andFilterWhere(['like', 'receiver', $this->receiver]);

        return $dataProvider;
    }
}
