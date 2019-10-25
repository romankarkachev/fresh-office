<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * pbxCallsSearch represents the model behind the search form about `common\models\pbxCalls`.
 */
class pbxCallsSearch extends pbxCalls
{
    /**
     * Предустановленный набор для сокращения времени выбора периода
     */
    const FILTER_PREDEFINED_PERIOD_TODAY = 1;
    const FILTER_PREDEFINED_PERIOD_YESTERDAY = 2;
    const FILTER_PREDEFINED_PERIOD_WEEK = 3;
    const FILTER_PREDEFINED_PERIOD_MONTH = 4;

    /**
     * Направления звонков (для отбора)
     */
    const FILTER_CALL_DIRECTION_ВСЕ = 1;
    const FILTER_CALL_DIRECTION_ВХОДЯЩИЙ = 2;
    const FILTER_CALL_DIRECTION_ИСХОДЯЩИЙ = 3;
    const FILTER_CALL_DIRECTION_ВНУТРЕННИЙ = 4;

    /**
     * Возможные значения для отбора по полю "Новый"
     */
    const FILTER_IS_NEW_ВСЕ = 1;
    const FILTER_IS_NEW_ТОЛЬКО_НОВЫЕ = 2;
    const FILTER_IS_NEW_ТОЛЬКО_НЕ_НОВЫЕ = 3;

    /**
     * Список возможных значений для идентификации звонков как входящих или исходящих
     */
    const FIELD_VALUES_FOR_DIRECTION_DETECTION = [
        'SIP/cifra1',
        'SIP/mango',
        'SIP/telphin',
        'SIP/youmagic',
        'Local/',
        'SIP/zadarma',
    ];

    /**
     * @var string дата начала периода
     */
    public $searchCallPeriodStart;

    /**
     * @var string дата окончания периода
     */
    public $searchCallPeriodEnd;

    /**
     * @var integer направление звонка (поле отбора)
     */
    public $searchCallDirection;

    /**
     * @var integer поле для отбора по полю "Новый"
     */
    public $searchOnlyNew;

    /**
     * @var string поле для отбора по нескольким полям
     */
    public $searchEntire;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'duration', 'billsec', 'amaflags', 'is_new', 'number_id', 'website_id', 'fo_ca_id', 'searchCallDirection', 'searchOnlyNew'], 'integer'],
            [['calldate', 'clid', 'src', 'dst', 'dcontext', 'channel', 'dstchannel', 'lastapp', 'lastdata', 'disposition', 'accountcode', 'uniqueid', 'userfield', 'searchCallPeriodStart', 'searchCallPeriodEnd', 'searchEntire'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'searchCallPeriodStart' => 'Начало периода',
            'searchCallPeriodEnd' => 'Конец периода',
            'searchCallDirection' => 'Направление звонка',
            'searchOnlyNew' => 'Новые',
            'searchEntire' => 'Универсальный поиск',
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
     * @return array
     */
    public static function fetchPredefinedPeriods()
    {
        return [
            [
                'id' => 'btnPredefinedToday',
                'label' => 'Сегодня',
                'options' => [
                    'data-id' => self::FILTER_PREDEFINED_PERIOD_TODAY,
                    'title' => 'Выбрать сегодняшний день в качестве периода',
                    'class' => 'btn btn-default',
                ],
            ],
            [
                'id' => 'btnPredefinedYesterday',
                'label' => 'Вчера',
                'options' => [
                    'data-id' => self::FILTER_PREDEFINED_PERIOD_YESTERDAY,
                    'title' => 'Выбрать вчерашний день в качестве периода',
                    'class' => 'btn btn-default',
                ],
            ],
            [
                'id' => 'btnPredefinedWeek',
                'label' => 'Неделя',
                'options' => [
                    'data-id' => self::FILTER_PREDEFINED_PERIOD_WEEK,
                    'title' => 'Вставить последние семь дней в качестве периода',
                    'class' => 'btn btn-default',
                ],
            ],
            [
                'id' => 'btnPredefinedMonth',
                'label' => 'Месяц',
                'options' => [
                    'data-id' => self::FILTER_PREDEFINED_PERIOD_MONTH,
                    'title' => 'Вставить сроки текущего месяца в качестве периода',
                    'class' => 'btn btn-default',
                ],
            ],
        ];
    }

    /**
     * Возвращает массив со значениями для отбора по направлению звонка.
     * @return array
     */
    public static function fetchFilterCallDirection()
    {
        return [
            [
                'id' => self::FILTER_CALL_DIRECTION_ВСЕ,
                'name' => 'Все',
                'hint' => 'Показать звонки вне зависимости от направления',
            ],
            [
                'id' => self::FILTER_CALL_DIRECTION_ВХОДЯЩИЙ,
                'name' => 'Входящие',
                'hint' => 'Показать только входящие в компанию звонки',
            ],
            [
                'id' => self::FILTER_CALL_DIRECTION_ИСХОДЯЩИЙ,
                'name' => 'Исходящие',
                'hint' => 'Показать только исходящие из компании звонки',
            ],
            [
                'id' => self::FILTER_CALL_DIRECTION_ВНУТРЕННИЙ,
                'name' => 'Внутренние',
                'hint' => 'Показать только звонки внутри компании',
            ],
        ];
    }

    /**
     * Возвращает массив со значениями для отбора по полю "Новый"
     * @return array
     */
    public static function fetchFilterIsNew()
    {
        return [
            [
                'id' => self::FILTER_IS_NEW_ВСЕ,
                'name' => 'Все',
            ],
            [
                'id' => self::FILTER_IS_NEW_ТОЛЬКО_НОВЫЕ,
                'name' => 'Только новые',
            ],
            [
                'id' => self::FILTER_IS_NEW_ТОЛЬКО_НЕ_НОВЫЕ,
                'name' => 'Только не новые',
            ],
        ];
    }

    /**
     * @param $condition
     * @param $field
     * @return array
     */
    public static function callDirectionFieldSet($condition, $field)
    {
        $result = [];

        foreach (self::FIELD_VALUES_FOR_DIRECTION_DETECTION as $value) {
            $result[] = [$condition, $field, $value . '%', false];
        }

        return $result;
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
        $query = pbxCalls::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => 'pbx-calls',
                'pageSize' => 50,
            ],
            'sort' => [
                'route' => 'pbx-calls',
                'defaultOrder' => ['calldate' => SORT_DESC],
                'attributes' => [
                    'id',
                    'calldate',
                    'clid',
                    'src',
                    'dst',
                    'dcontext',
                    'channel',
                    'dstchannel',
                    'lastapp',
                    'lastdata',
                    'duration',
                    'billsec',
                    'disposition',
                    'amaflags',
                    'accountcode',
                    'uniqueid',
                    'userfield',
                    'is_new',
                    'number_id',
                    'website_id',
                    'websiteName' => [
                        'asc' => ['websites.name' => SORT_ASC],
                        'desc' => ['websites.name' => SORT_DESC],
                    ],
                    'regionName' => [
                        'asc' => ['numbers.region' => SORT_ASC],
                        'desc' => ['numbers.region' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['website', 'number']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // значения по-умолчанию
        // направление звонка
        if (empty($this->searchCallDirection)) {
            $this->searchCallDirection = self::FILTER_CALL_DIRECTION_ВСЕ;
        }

        if (empty($this->searchCallPeriodStart) && empty($this->searchCallPeriodEnd)) {
            $this->searchCallPeriodStart = date('Y-m-d', time());
            $this->searchCallPeriodEnd = date('Y-m-d', time());
        }

        // отбор по полю "Новый"
        if (empty($this->searchOnlyNew)) {
            $this->searchOnlyNew = self::FILTER_IS_NEW_ВСЕ;
        }

        // дополним условием по направлению звонка
        switch ($this->searchCallDirection) {
            case self::FILTER_CALL_DIRECTION_ВСЕ:
                $query->andFilterWhere([
                    'or',
                    ['like', 'channel', $this->channel],
                    ['like', 'dstchannel', $this->dstchannel],
                ]);
                break;
            case self::FILTER_CALL_DIRECTION_ВХОДЯЩИЙ:
                $query->andFilterWhere(ArrayHelper::merge(['or'], self::callDirectionFieldSet('like', 'channel')));
                break;
            case self::FILTER_CALL_DIRECTION_ИСХОДЯЩИЙ:
                $query->andFilterWhere(ArrayHelper::merge(['or'], self::callDirectionFieldSet('like', 'dstchannel')));
                break;
            case self::FILTER_CALL_DIRECTION_ВНУТРЕННИЙ:
                $query->andFilterWhere(ArrayHelper::merge(['and'], self::callDirectionFieldSet('not like', 'channel')));
                $query->andFilterWhere(ArrayHelper::merge(['and'], self::callDirectionFieldSet('not like', 'dstchannel')));
                break;
        }

        // дополним запрос условием по полю "Новый"
        switch ($this->searchOnlyNew) {
            case self::FILTER_IS_NEW_ВСЕ:
                $query->andFilterWhere([
                    'is_new' => $this->is_new,
                ]);
                break;
            case self::FILTER_IS_NEW_ТОЛЬКО_НОВЫЕ:
                $query->andFilterWhere([
                    'is_new' => true,
                ]);
                break;
            case self::FILTER_IS_NEW_ТОЛЬКО_НЕ_НОВЫЕ:
                $query->andFilterWhere([
                    'is_new' => false,
                ]);
                break;
        }

        // возможный отбор за период
        if (!empty($this->searchCallPeriodStart) || !empty($this->searchCallPeriodEnd)) {
            if (!empty($this->searchCallPeriodStart) && !empty($this->searchCallPeriodEnd)) {
                // если указаны обе даты
                $query->andFilterWhere(['between', pbxCalls::tableName() . '.calldate', $this->searchCallPeriodStart . ' 00:00:00', $this->searchCallPeriodEnd . ' 23:59:59']);
            }
            elseif (!empty($this->searchCallPeriodStart) && empty($this->searchCallPeriodEnd)) {
                // если указано только начало периода
                $query->andFilterWhere(['>=', pbxCalls::tableName() . '.calldate', $this->searchCallPeriodStart . ' 00:00:00']);
            }
            elseif (empty($this->searchCallPeriodStart) && !empty($this->searchCallPeriodEnd)) {
                // если указан только конец периода
                $query->andFilterWhere(['<=', pbxCalls::tableName() . '.calldate', $this->searchCallPeriodEnd . ' 23:59:59']);
            }
        }
        else {
            $query->andFilterWhere([
                'calldate' => $this->calldate,
            ]);
        }

        // поле для универсального поиска
        if (!empty($this->searchEntire)) {
            $query->andFilterWhere([
                'or',
                ['like', 'src', $this->searchEntire],
                //['like', 'dst', $this->searchEntire],
            ]);
        }
        else {
            $query->andFilterWhere(['like', 'src', $this->src])
                ->andFilterWhere(['like', 'dst', $this->dst]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'duration' => $this->duration,
            'billsec' => $this->billsec,
            'amaflags' => $this->amaflags,
            'number_id' => $this->number_id,
            'website_id' => $this->website_id,
            'fo_ca_id' => $this->fo_ca_id,
        ]);

        $query->andFilterWhere(['like', 'clid', $this->clid])
            ->andFilterWhere(['like', 'dcontext', $this->dcontext])
            ->andFilterWhere(['like', 'channel', $this->channel])
            ->andFilterWhere(['like', 'dstchannel', $this->dstchannel])
            ->andFilterWhere(['like', 'lastapp', $this->lastapp])
            ->andFilterWhere(['like', 'lastdata', $this->lastdata])
            ->andFilterWhere(['like', 'disposition', $this->disposition])
            ->andFilterWhere(['like', 'accountcode', $this->accountcode])
            ->andFilterWhere(['like', 'uniqueid', $this->uniqueid])
            ->andFilterWhere(['like', 'userfield', $this->userfield]);

        return $dataProvider;
    }
}
