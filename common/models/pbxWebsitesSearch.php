<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\controllers\PbxWebsitesController;

/**
 * pbxWebsitesSearch represents the model behind the search form about `common\models\pbxWebsites`.
 */
class pbxWebsitesSearch extends pbxWebsites
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name'], 'safe'],
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
        $query = pbxWebsites::find()->select([
            'id' => pbxWebsites::tableName() . '.id',
            'name',
            'phonesCount' => '(
                SELECT COUNT(`' . pbxExternalPhoneNumber::tableName() . '`.`id`) FROM `' . pbxExternalPhoneNumber::tableName() . '`
                WHERE `' . pbxExternalPhoneNumber::tableName() . '`.`website_id` = `' . pbxWebsites::tableName() . '`.`id`
            )',
        ]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => PbxWebsitesController::ROOT_URL_FOR_SORT_PAGING,
                'pageSize' => 50,
            ],
            'sort' => [
                'route' => PbxWebsitesController::ROOT_URL_FOR_SORT_PAGING,
                'defaultOrder' => ['name' => SORT_ASC],
                'attributes' => [
                    'id',
                    'name',
                    'phonesCount',
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
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
