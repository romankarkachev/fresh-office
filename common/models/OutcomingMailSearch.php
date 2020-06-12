<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * OutcomingMailSearch represents the model behind the search form of `common\models\IncomingMail`.
 */
class OutcomingMailSearch extends IncomingMail
{
    /**
     * @var string поле отбора, определяющее начало периода
     */
    public $searchCreatedAtStart;

    /**
     * @var string поле отбора, определяющее окончания периода
     */
    public $searchCreatedAtEnd;

    /**
     * @var string поле отбора, определяющее начало периода
     */
    public $searchSentStart;

    /**
     * @var string поле отбора, определяющее окончания периода
     */
    public $searchSentEnd;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'type_id', 'org_id', 'ca_src', 'ca_id', 'receiver_id'], 'integer'],
            [[
                'inc_num', 'inc_date', 'description', 'ca_name', 'comment',
                'searchCreatedAtStart', 'searchCreatedAtEnd', 'searchSentStart', 'searchSentEnd',
            ], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'searchCreatedAtStart' => 'Создан с',
            'searchCreatedAtEnd' => 'Создан по',
            'searchSentStart' => 'Получен с',
            'searchSentEnd' => 'Получен по',
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
     * Возвращает набор атрибутов, по которым можно сортировать таблицу исходящей корреспонденции.
     * @return array
     */
    public static function sortAttributes()
    {
        return [
            'id' => [
                'asc' => [self::tableName() . '.id' => SORT_ASC],
                'desc' => [self::tableName() . '.id' => SORT_DESC],
            ],
            'created_at',
            'created_by',
            'state_id',
            'inc_num',
            'inc_date',
            'type_id',
            'org_id',
            'description',
            'ca_src',
            'ca_id',
            'ca_name',
            'receiver_id',
            'comment',
            'createdByProfileName' => [
                'asc' => ['createdByProfile.name' => SORT_ASC],
                'desc' => ['createdByProfile.name' => SORT_DESC],
            ],
            'stateName' => [
                'asc' => [ProjectsStates::tableName() . '.name' => SORT_ASC],
                'desc' => [ProjectsStates::tableName() . '.name' => SORT_DESC],
            ],
            'typeName' => [
                'asc' => [IncomingMailTypes::tableName() . '.name' => SORT_ASC],
                'desc' => [IncomingMailTypes::tableName() . '.name' => SORT_DESC],
            ],
            'organizationName' => [
                'asc' => [Organizations::tableName() . '.name' => SORT_ASC],
                'desc' => [Organizations::tableName() . '.name' => SORT_DESC],
            ],
            'receiverName' => [
                'asc' => ['receiverProfile.name' => SORT_ASC],
                'desc' => ['receiverProfile.name' => SORT_DESC],
            ],

        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param $route string маршрут для сортировки и постраничного перехода
     * @return ActiveDataProvider
     */
    public function search($params, $route = 'outcoming-mail')
    {
        $tableName = IncomingMail::tableName();
        // обязательное условие, которое не должно быть отменено, отбор только исходящей корреспонденции
        $query = IncomingMail::find()->where(['direction' => IncomingMail::DIRECTION_OUT]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => $route,
                'pageSize' => 50,
            ],
            'sort' => [
                'route' => $route,
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => self::sortAttributes(),
            ],
        ]);

        $this->load($params);
        $query->joinWith(['createdByProfile', 'state', 'type', 'organization', 'receiverProfile']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // дополним условием отбора за период по дате создания
        if (!empty($this->searchCreatedAtStart) || !empty($this->searchCreatedAtEnd)) {
            if (!empty($this->searchCreatedAtStart) && !empty($this->searchCreatedAtEnd)) {
                // если указаны обе даты
                $query->andFilterWhere(['between', $tableName . '.created_at', strtotime($this->searchCreatedAtStart . ' 00:00:00'), strtotime($this->searchCreatedAtEnd . ' 23:59:59')]);
            } else if (!empty($this->searchCreatedAtStart) && empty($this->searchCreatedAtEnd)) {
                // если указано только начало периода
                $query->andFilterWhere(['>=', $tableName . '.created_at', strtotime($this->searchCreatedAtStart . ' 00:00:00')]);
            } else if (empty($this->searchCreatedAtStart) && !empty($this->searchCreatedAtEnd)) {
                // если указан только конец периода
                $query->andFilterWhere(['<=', $tableName . '.created_at', strtotime($this->searchCreatedAtEnd . ' 23:59:59')]);
            };
        }
        else {
            $query->andFilterWhere([
                'created_at' => $this->created_at,
            ]);
        }

        // дополним условием отбора за период по дате получения
        if (!empty($this->searchSentStart) || !empty($this->searchSentEnd)) {
            if (!empty($this->searchSentStart) && !empty($this->searchSentEnd)) {
                // если указаны обе даты
                $query->andFilterWhere(['between', $tableName . '.inc_date', $this->searchSentStart . ' 00:00:00', $this->searchSentEnd . ' 23:59:59']);
            } else if (!empty($this->searchSentStart) && empty($this->searchSentEnd)) {
                // если указано только начало периода
                $query->andFilterWhere(['>=', $tableName . '.inc_date', $this->searchSentStart . ' 00:00:00']);
            } else if (empty($this->searchSentStart) && !empty($this->searchSentEnd)) {
                // если указан только конец периода
                $query->andFilterWhere(['<=', $tableName . '.inc_date', $this->searchSentEnd . ' 23:59:59']);
            };
        }
        else {
            $query->andFilterWhere([
                'inc_date' => $this->inc_date,
            ]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'created_by' => $this->created_by,
            'type_id' => $this->type_id,
            'org_id' => $this->org_id,
            'ca_src' => $this->ca_src,
            'ca_id' => $this->ca_id,
            'receiver_id' => $this->receiver_id,
        ]);

        $query->andFilterWhere(['like', 'inc_num', $this->inc_num])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'ca_name', $this->ca_name])
            ->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
