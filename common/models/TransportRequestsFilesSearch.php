<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\TransportRequestsFiles;

/**
 * TransportRequestsFilesSearch represents the model behind the search form about `common\models\TransportRequestsFiles`.
 */
class TransportRequestsFilesSearch extends TransportRequestsFiles
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'uploaded_at', 'uploaded_by', 'tr_id', 'size'], 'integer'],
            [['ffp', 'fn', 'ofn'], 'safe'],
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
        $query = TransportRequestsFiles::find();

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
            'uploaded_at' => $this->uploaded_at,
            'uploaded_by' => $this->uploaded_by,
            'tr_id' => $this->tr_id,
            'size' => $this->size,
        ]);

        $query->andFilterWhere(['like', 'ffp', $this->ffp])
            ->andFilterWhere(['like', 'fn', $this->fn])
            ->andFilterWhere(['like', 'ofn', $this->ofn]);

        return $dataProvider;
    }
}