<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\TendersFiles;

/**
 * TendersFilesSearch represents the model behind the search form of `common\models\TendersFiles`.
 */
class TendersFilesSearch extends TendersFiles
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'uploaded_at', 'uploaded_by', 'tender_id', 'size', 'ct_id'], 'integer'],
            [['ffp', 'fn', 'ofn', 'revision', 'src_id'], 'safe'],
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
        $query = TendersFiles::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if ($this->ct_id > 0) {
            $query->andFilterWhere([
                'ct_id' => $this->ct_id,
            ]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'uploaded_at' => $this->uploaded_at,
            'uploaded_by' => $this->uploaded_by,
            'tender_id' => $this->tender_id,
            'size' => $this->size,
        ]);

        $query->andFilterWhere(['like', 'ffp', $this->ffp])
            ->andFilterWhere(['like', 'fn', $this->fn])
            ->andFilterWhere(['like', 'ofn', $this->ofn])
            ->andFilterWhere(['like', 'revision', $this->revision])
            ->andFilterWhere(['like', 'src_id', $this->src_id]);

        return $dataProvider;
    }
}
