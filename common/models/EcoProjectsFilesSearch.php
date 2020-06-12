<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\EcoProjectsFiles;

/**
 * EcoProjectsFilesSearch represents the model behind the search form of `common\models\EcoProjectsFiles`.
 */
class EcoProjectsFilesSearch extends EcoProjectsFiles
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'uploaded_at', 'uploaded_by', 'project_id', 'size'], 'integer'],
            [['ffp', 'fn', 'ofn'], 'safe'],
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
        $query = EcoProjectsFiles::find();
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
            'project_id' => $this->project_id,
            'size' => $this->size,
        ]);

        $query->andFilterWhere(['like', 'ffp', $this->ffp])
            ->andFilterWhere(['like', 'fn', $this->fn])
            ->andFilterWhere(['like', 'ofn', $this->ofn]);

        return $dataProvider;
    }
}
