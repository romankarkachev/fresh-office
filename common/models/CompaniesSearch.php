<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use backend\controllers\CompaniesController;

/**
 * CompaniesSearch represents the model behind the search form of `common\models\Companies`.
 */
class CompaniesSearch extends Companies
{
    /**
     * @var string поле для универсального поиска
     */
    public $searchEntire;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by'], 'integer'],
            [['guid', 'name', 'name_full', 'name_short', 'inn', 'kpp', 'ogrn', 'address_j', 'address_f', 'dir_post', 'dir_name', 'dir_name_of', 'dir_name_short', 'dir_name_short_of', 'comment', 'searchEntire'], 'safe'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'searchEntire' => 'Универсальный поиск',
        ]);
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
     * @param array $params
     * @param $route string URL для перехода в список записей
     * @return ActiveDataProvider
     */
    public function search($params, $route = CompaniesController::ROOT_URL_FOR_SORT_PAGING)
    {
        $query = Companies::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => $route,
                'pageSize' => 50,
            ],
            'sort' => [
                'route' => $route,
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'id',
                    'created_at',
                    'created_by',
                    'guid',
                    'name',
                    'name_full',
                    'name_short',
                    'inn',
                    'kpp',
                    'ogrn',
                    'address_j',
                    'address_f',
                    'dir_post',
                    'dir_name',
                    'dir_name_of',
                    'dir_name_short',
                    'dir_name_short_of',
                    'comment',
                    'createdByProfileName' => [
                        'asc' => ['createdByProfile.name' => SORT_ASC],
                        'desc' => ['createdByProfile.name' => SORT_DESC],
                    ],
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
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
        ]);

        if (!empty($this->searchEntire)) {
            // применяется универсальный поиск
            $query->andWhere([
                'or',
                ['like', 'guid', $this->searchEntire],
                ['like', 'name', $this->searchEntire],
                ['like', 'name_full', $this->searchEntire],
                ['like', 'name_short', $this->searchEntire],
                ['like', 'inn', $this->searchEntire],
                ['like', 'kpp', $this->searchEntire],
                ['like', 'ogrn', $this->searchEntire],
            ]);
        }
        else {
            $query->andFilterWhere(['like', 'guid', $this->guid])
                ->andFilterWhere(['like', 'name', $this->name])
                ->andFilterWhere(['like', 'name_full', $this->name_full])
                ->andFilterWhere(['like', 'name_short', $this->name_short])
                ->andFilterWhere(['like', 'inn', $this->inn])
                ->andFilterWhere(['like', 'kpp', $this->kpp])
                ->andFilterWhere(['like', 'ogrn', $this->ogrn])
                ->andFilterWhere(['like', 'address_j', $this->address_j])
                ->andFilterWhere(['like', 'address_f', $this->address_f])
                ->andFilterWhere(['like', 'dir_post', $this->dir_post])
                ->andFilterWhere(['like', 'dir_name', $this->dir_name])
                ->andFilterWhere(['like', 'dir_name_of', $this->dir_name_of])
                ->andFilterWhere(['like', 'dir_name_short', $this->dir_name_short])
                ->andFilterWhere(['like', 'dir_name_short_of', $this->dir_name_short_of])
                ->andFilterWhere(['like', 'comment', $this->comment]);
        }

        return $dataProvider;
    }
}
