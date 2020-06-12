<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\TenderFormsKindsFields;

/**
 * TenderFormsKindsFieldsSearch represents the model behind the search form of `common\models\TenderFormsKindsFields`.
 */
class TenderFormsKindsFieldsSearch extends TenderFormsKindsFields
{
    /**
     * @var int идентификатор разновидности
     */
    public $searchByVariety;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'kind_id', 'searchByVariety'], 'integer'],
            [['alias', 'name', 'description', 'widget'], 'safe'],
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
        $query = TenderFormsKindsFields::find()->orderBy([TenderFormsKinds::tableName() . '.name' => SORT_ASC]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);
        $joinWith = ['kind'];

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'kind_id' => $this->kind_id,
        ]);

        if (!empty($this->searchByVariety)) {
            $joinWith[]  = 'vk';
            $query->andWhere([
                TenderFormsVarietiesKinds::tableName() . '.`variety_id`' => $this->searchByVariety,
            ]);
        }

        $query->andFilterWhere(['like', 'alias', $this->alias])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'widget', $this->widget]);

        $query->joinWith($joinWith);
        return $dataProvider;
    }
}
