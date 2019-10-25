<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * AppealsSearch represents the model behind the search form about `common\models\Appeals`.
 */
class AppealsSearch extends Appeals
{
    /**
     * @var string дата начала периода для отбора
     */
    public $searchPeriodStart;

    /**
     * @var string дата окончания периода для отбора
     */
    public $searchPeriodEnd;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'state_id', 'ac_id', 'fo_id_company', 'fo_id_manager', 'ca_state_id', 'as_id'], 'integer'],
            [['form_company', 'form_username', 'form_region', 'form_phone', 'form_email', 'form_message', 'fo_company_name', 'request_referrer', 'request_user_agent', 'request_user_ip', 'searchPeriodStart', 'searchPeriodEnd'], 'safe'],
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
        $query = Appeals::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => 'appeals',
            ],
        ]);

        $dataProvider->setSort([
            'route' => 'appeals',
            'defaultOrder' => ['created_at' => SORT_DESC],
            'attributes' => [
                'id',
                'created_at',
                'created_by',
                'state_id',
                'ac_id',
                'form_company',
                'form_username',
                'form_region',
                'form_phone',
                'form_email',
                'form_message',
                'fo_id_company',
                'fo_company_name',
                'fo_id_manager',
                'ca_state_id',
                'as_id',
                'request_referrer',
                'request_user_agent',
                'request_user_ip',
                //'appealStateName',
                //'caStateName',
                'createdByProfileName' => [
                    'asc' => ['profile.name' => SORT_ASC],
                    'desc' => ['profile.name' => SORT_DESC],
                ],
                'appealSourceName' => [
                    'asc' => ['appeal_sources.name' => SORT_ASC],
                    'desc' => ['appeal_sources.name' => SORT_DESC],
                ],
            ]
        ]);

        $this->load($params);
        $query->joinWith(['createdByProfile', 'as']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // для операторов отбор только по собственным обращениям
        if (Yii::$app->user->can('operator')) {
            // для роли "Старший оператор" особые условия
            if (Yii::$app->user->can('operator_head')) {
                $query->orWhere(['as_id' => AppealSources::ИСТОЧНИК_1NOK]);
            }
            $query->orWhere(['created_by' => Yii::$app->user->id]);
        }

        // возможный отбор за период
        if (!empty($this->searchPeriodStart) || !empty($this->searchPeriodEnd)) {
            if (!empty($this->searchPeriodStart) && !empty($this->searchPeriodEnd)) {
                // если указаны обе даты
                $query->andFilterWhere(['between', Appeals::tableName() . '.created_at', strtotime($this->searchPeriodStart . ' 00:00:00'), strtotime($this->searchPeriodEnd . ' 23:59:59')]);
            }
            elseif (!empty($this->searchPeriodStart) && empty($this->searchPeriodEnd)) {
                // если указано только начало периода
                $query->andFilterWhere(['>=', Appeals::tableName() . '.created_at', strtotime($this->searchPeriodStart . ' 00:00:00')]);
            }
            elseif (empty($this->searchPeriodStart) && !empty($this->searchPeriodEnd)) {
                // если указан только конец периода
                $query->andFilterWhere(['<=', Appeals::tableName() . '.created_at', strtotime($this->searchPeriodEnd . ' 23:59:59')]);
            }
        }
        else {
            $query->andFilterWhere([
                'created_at' => $this->created_at,
            ]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'created_by' => $this->created_by,
            'state_id' => $this->state_id,
            'ac_id' => $this->ac_id,
            'fo_id_company' => $this->fo_id_company,
            'fo_id_manager' => $this->fo_id_manager,
            'ca_state_id' => $this->ca_state_id,
            'as_id' => $this->as_id,
        ]);

        $query->andFilterWhere(['like', 'form_company', $this->form_company])
            ->andFilterWhere(['like', 'form_username', $this->form_username])
            ->andFilterWhere(['like', 'form_region', $this->form_region])
            ->andFilterWhere(['like', 'form_phone', $this->form_phone])
            ->andFilterWhere(['like', 'form_email', $this->form_email])
            ->andFilterWhere(['like', 'form_message', $this->form_message])
            ->andFilterWhere(['like', 'fo_company_name', $this->fo_company_name]);

        return $dataProvider;
    }
}
