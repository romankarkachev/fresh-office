<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\DesktopWidgetsAccess;

/**
 * DesktopWidgetsAccessSearch represents the model behind the search form of `common\models\DesktopWidgetsAccess`.
 */
class DesktopWidgetsAccessSearch extends DesktopWidgetsAccess
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'widget_id', 'type', 'entity_id'], 'integer'],
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
        $query = DesktopWidgetsAccess::find();
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
            'widget_id' => $this->widget_id,
            'type' => $this->type,
            'entity_id' => $this->entity_id,
        ]);

        return $dataProvider;
    }
}
