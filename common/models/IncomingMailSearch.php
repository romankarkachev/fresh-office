<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * IncomingMailSearch represents the model behind the search form of `common\models\IncomingMail`.
 */
class IncomingMailSearch extends IncomingMail
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
    public $searchCompleteBeforeStart;

    /**
     * @var string поле отбора, определяющее окончания периода
     */
    public $searchCompleteBeforeEnd;

    /**
     * @var string поле отбора, определяющее начало периода
     */
    public $searchReceivedStart;

    /**
     * @var string поле отбора, определяющее окончания периода
     */
    public $searchReceivedEnd;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'type_id', 'org_id', 'ca_src', 'ca_id', 'receiver_id'], 'integer'],
            [[
                'inc_num', 'inc_date', 'description', 'date_complete_before', 'ca_name', 'comment',
                'searchCreatedAtStart', 'searchCreatedAtEnd', 'searchCompleteBeforeStart', 'searchCompleteBeforeEnd', 'searchReceivedStart', 'searchReceivedEnd',
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
            'searchCompleteBeforeStart' => 'Срок с',
            'searchCompleteBeforeEnd' => 'Срок по',
            'searchReceivedStart' => 'Получен с',
            'searchReceivedEnd' => 'Получен по',
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
     * Возвращает набор атрибутов, по которым можно сортировать таблицу входящей корреспонденции.
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
            'date_complete_before',
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
    public function search($params, $route = 'incoming-mail')
    {
        $tableName = IncomingMail::tableName();
        // обязательное условие, которое не должно быть отменено, отбор только входящей корреспонденции
        $query = IncomingMail::find()->where(['direction' => IncomingMail::DIRECTION_IN]);
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
        $query->joinWith(['createdByProfile', 'type', 'organization', 'receiverProfile']);

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

        // дополним условием отбора за период по дате окончания срока рассмотрения
        if (!empty($this->searchCompleteBeforeStart) || !empty($this->searchCompleteBeforeEnd)) {
            if (!empty($this->searchCompleteBeforeStart) && !empty($this->searchCompleteBeforeEnd)) {
                // если указаны обе даты
                $query->andFilterWhere(['between', $tableName . '.date_complete_before', $this->searchCompleteBeforeStart . ' 00:00:00', $this->searchCompleteBeforeEnd . ' 23:59:59']);
            } else if (!empty($this->searchCompleteBeforeStart) && empty($this->searchCompleteBeforeEnd)) {
                // если указано только начало периода
                $query->andFilterWhere(['>=', $tableName . '.date_complete_before', $this->searchCompleteBeforeStart . ' 00:00:00']);
            } else if (empty($this->searchCompleteBeforeStart) && !empty($this->searchCompleteBeforeEnd)) {
                // если указан только конец периода
                $query->andFilterWhere(['<=', $tableName . '.date_complete_before', $this->searchCompleteBeforeEnd . ' 23:59:59']);
            };
        }
        else {
            $query->andFilterWhere([
                'date_complete_before' => $this->date_complete_before,
            ]);
        }

        // дополним условием отбора за период по дате получения
        if (!empty($this->searchReceivedStart) || !empty($this->searchReceivedEnd)) {
            if (!empty($this->searchReceivedStart) && !empty($this->searchReceivedEnd)) {
                // если указаны обе даты
                $query->andFilterWhere(['between', $tableName . '.inc_date', $this->searchReceivedStart . ' 00:00:00', $this->searchReceivedEnd . ' 23:59:59']);
            } else if (!empty($this->searchReceivedStart) && empty($this->searchReceivedEnd)) {
                // если указано только начало периода
                $query->andFilterWhere(['>=', $tableName . '.inc_date', $this->searchReceivedStart . ' 00:00:00']);
            } else if (empty($this->searchReceivedStart) && !empty($this->searchReceivedEnd)) {
                // если указан только конец периода
                $query->andFilterWhere(['<=', $tableName . '.inc_date', $this->searchReceivedEnd . ' 23:59:59']);
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

        if (\Yii::$app->user->can(AuthItem::ROLE_LOGIST)) {
            if (!empty($this->ca_name)) {
                // для логистов отбор только по перевозчикам
                $query->andWhere([
                    'and',
                    ['ca_src' => self::CA_SOURCES_ПЕРЕВОЗЧИКИ],
                    ['like', 'ca_name', $this->ca_name],
                ]);
            }
            else {
                $query->andWhere(['ca_src' => self::CA_SOURCES_ПЕРЕВОЗЧИКИ]);
            }
        }
        else {
            $query->andFilterWhere(['like', 'ca_name', $this->ca_name]);
        }

        $query->andFilterWhere(['like', 'inc_num', $this->inc_num])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
