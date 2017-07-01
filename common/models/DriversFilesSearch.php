<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Drivers;

/**
 * DriversFilesSearch represents the model behind the search form about `common\models\DriversFiles`.
 */
class DriversFilesSearch extends DriversFiles
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'ferryman_id'], 'integer'],
            [['surname', 'name', 'patronymic', 'driver_license', 'dl_issued_at', 'driver_license_index', 'phone', 'pass_serie', 'pass_num', 'pass_issued_at', 'pass_issued_by'], 'safe'],
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
        $query = Drivers::find();

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
            'ferryman_id' => $this->ferryman_id,
            'dl_issued_at' => $this->dl_issued_at,
            'pass_issued_at' => $this->pass_issued_at,
        ]);

        $query->andFilterWhere(['like', 'surname', $this->surname])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'patronymic', $this->patronymic])
            ->andFilterWhere(['like', 'driver_license', $this->driver_license])
            ->andFilterWhere(['like', 'driver_license_index', $this->driver_license_index])
            ->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(['like', 'pass_serie', $this->pass_serie])
            ->andFilterWhere(['like', 'pass_num', $this->pass_num])
            ->andFilterWhere(['like', 'pass_issued_by', $this->pass_issued_by]);

        return $dataProvider;
    }
}
