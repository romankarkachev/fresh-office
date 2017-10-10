<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\ProductionFeedbackFiles;
use yii\helpers\ArrayHelper;

/**
 * ProductionFeedbackFilesSearch represents the model behind the search form about `common\models\ProductionFeedbackFiles`.
 */
class ProductionFeedbackFilesSearch extends ProductionFeedbackFiles
{
    /**
     * Дата начала периода.
     * @var string
     */
    public $searchPeriodStart;

    /**
     * Дата окончания периода.
     * @var string
     */
    public $searchPeriodEnd;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'uploaded_at', 'uploaded_by', 'project_id', 'ca_id', 'size'], 'integer'],
            [['ffp', 'fn', 'ofn', 'searchPeriodStart', 'searchPeriodEnd'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'searchPeriodStart' => 'Начало периода',
            'searchPeriodEnd' => 'Конец периода',
        ]);
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
        $query = ProductionFeedbackFiles::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => $params['route'],
                'pageSize' => $params['pageSize'],
            ],
            'sort' => [
                'route' => '/transport-requests/production-feedback-files',
                'defaultOrder' => ['uploaded_at' => SORT_DESC],
                'attributes' => [
                    'id',
                    'uploaded_at',
                    'uploaded_by',
                    'action',
                    'project_id',
                    'ca_id',
                    'thumb_ffp',
                    'thumb_fn',
                    'ffp',
                    'fn',
                    'ofn',
                    'size',
                    'uploadedByName' => [
                        'asc' => ['profile.name' => SORT_ASC],
                        'desc' => ['profile.name' => SORT_DESC],
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

        if ($this->searchPeriodStart != null or $this->searchPeriodEnd != null) {
            // если указаны обе даты
            $query->andWhere(['between', 'uploaded_at', strtotime($this->searchPeriodStart . ' 00:00:00'), strtotime($this->searchPeriodEnd . ' 23:59:59')]);
        } else if ($this->searchPeriodStart != null && $this->searchPeriodEnd == null) {
            // если указан только начало периода
            $query->andWhere(['>=', 'uploaded_at', strtotime($this->searchPeriodStart . ' 00:00:00')]);
        } else if ($this->searchPeriodStart == null && $this->searchPeriodEnd != null) {
            // если указан только конец периода
            $query->andWhere(['<=', 'uploaded_at', strtotime($this->searchPeriodEnd . ' 23:59:59')]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'uploaded_by' => $this->uploaded_by,
            'project_id' => $this->project_id,
            'ca_id' => $this->ca_id,
            'size' => $this->size,
        ]);

        $query->andFilterWhere(['like', 'ffp', $this->ffp])
            ->andFilterWhere(['like', 'fn', $this->fn])
            ->andFilterWhere(['like', 'ofn', $this->ofn]);

        return $dataProvider;
    }
}
