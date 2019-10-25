<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\YandexSpeechKitRecognitionQueue;

/**
 * YandexSpeechKitRecognitionQueueSearch represents the model behind the search form of `common\models\YandexSpeechKitRecognitionQueue`.
 */
class YandexSpeechKitRecognitionQueueSearch extends YandexSpeechKitRecognitionQueue
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'check_after', 'call_id'], 'integer'],
            [['url_bucket', 'operation_id'], 'safe'],
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
        $query = YandexSpeechKitRecognitionQueue::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                //'route' => $route,
                'pageSize' => 50,
            ],
            'sort' => [
                //'route' => $route,
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'id',
                    'created_at',
                    'created_by',
                    'check_after',
                    'call_id',
                    'url_bucket',
                    'operation_id',
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
            'check_after' => $this->check_after,
            'call_id' => $this->call_id,
        ]);

        $query->andFilterWhere(['like', 'url_bucket', $this->url_bucket])
            ->andFilterWhere(['like', 'operation_id', $this->operation_id]);

        return $dataProvider;
    }
}
