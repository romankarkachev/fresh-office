<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Organizations;

/**
 * OrganizationsSearch represents the model behind the search form about `common\models\Organizations`.
 */
class OrganizationsSearch extends Organizations
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name', 'name_short', 'name_full', 'inn', 'kpp', 'ogrn', 'address_j', 'address_f', 'address_ttn', 'doc_num_tmpl', 'dir_post', 'dir_name', 'dir_name_short', 'dir_name_of', 'phones', 'email', 'license_req'], 'safe'],
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
        $query = Organizations::find();
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
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'name_short', $this->name_short])
            ->andFilterWhere(['like', 'name_full', $this->name_full])
            ->andFilterWhere(['like', 'inn', $this->inn])
            ->andFilterWhere(['like', 'kpp', $this->kpp])
            ->andFilterWhere(['like', 'ogrn', $this->ogrn])
            ->andFilterWhere(['like', 'address_j', $this->address_j])
            ->andFilterWhere(['like', 'address_f', $this->address_f])
            ->andFilterWhere(['like', 'address_ttn', $this->address_ttn])
            ->andFilterWhere(['like', 'doc_num_tmpl', $this->doc_num_tmpl])
            ->andFilterWhere(['like', 'dir_post', $this->dir_post])
            ->andFilterWhere(['like', 'dir_name', $this->dir_name])
            ->andFilterWhere(['like', 'dir_name_short', $this->dir_name_short])
            ->andFilterWhere(['like', 'dir_name_of', $this->dir_name_of])
            ->andFilterWhere(['like', 'phones', $this->phones])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'license_req', $this->license_req]);

        return $dataProvider;
    }
}
