<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\foCompanyContactPersons;

/**
 * foCompanyContactPersonsSearch represents the model behind the search form of `common\models\foCompanyContactPersons`.
 */
class foCompanyContactPersonsSearch extends foCompanyContactPersons
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ID_CONTACT_MAN', 'ID_COMPANY', 'ID_LIST_STATUS_CONTACT_MAN', 'TRASH', 'IS_DEFAULT'], 'integer'],
            [['CONTACT_MAN_NAME', 'DISCRIPTION_CONTACT_MAN', 'DISCRIPTION_CONTACT_MAN2', 'DATA_HAPY', 'NAME_PART', 'FAM_PART', 'OTCH_PART', 'RECOM_TIME_CONTACT', 'MANAGER_TRASH', 'DATE_TRASH', 'SEX', 'STATUS_CONTACT_MAN_DATE_CHANGED', 'CABINET_LOGIN', 'CABINET_PASS'], 'safe'],
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
        $query = foCompanyContactPersons::find();
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
            'ID_CONTACT_MAN' => $this->ID_CONTACT_MAN,
            'ID_COMPANY' => $this->ID_COMPANY,
            'DATA_HAPY' => $this->DATA_HAPY,
            'ID_LIST_STATUS_CONTACT_MAN' => $this->ID_LIST_STATUS_CONTACT_MAN,
            'TRASH' => $this->TRASH,
            'DATE_TRASH' => $this->DATE_TRASH,
            'STATUS_CONTACT_MAN_DATE_CHANGED' => $this->STATUS_CONTACT_MAN_DATE_CHANGED,
            'IS_DEFAULT' => $this->IS_DEFAULT,
        ]);

        $query->andFilterWhere(['like', 'CONTACT_MAN_NAME', $this->CONTACT_MAN_NAME])
            ->andFilterWhere(['like', 'DISCRIPTION_CONTACT_MAN', $this->DISCRIPTION_CONTACT_MAN])
            ->andFilterWhere(['like', 'DISCRIPTION_CONTACT_MAN2', $this->DISCRIPTION_CONTACT_MAN2])
            ->andFilterWhere(['like', 'NAME_PART', $this->NAME_PART])
            ->andFilterWhere(['like', 'FAM_PART', $this->FAM_PART])
            ->andFilterWhere(['like', 'OTCH_PART', $this->OTCH_PART])
            ->andFilterWhere(['like', 'RECOM_TIME_CONTACT', $this->RECOM_TIME_CONTACT])
            ->andFilterWhere(['like', 'MANAGER_TRASH', $this->MANAGER_TRASH])
            ->andFilterWhere(['like', 'SEX', $this->SEX])
            ->andFilterWhere(['like', 'CABINET_LOGIN', $this->CABINET_LOGIN])
            ->andFilterWhere(['like', 'CABINET_PASS', $this->CABINET_PASS]);

        return $dataProvider;
    }
}
