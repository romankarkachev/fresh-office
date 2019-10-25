<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * TendersLogsSearch represents the model behind the search form of `common\models\TendersLogs`.
 */
class TendersLogsSearch extends TendersLogs
{
    /**
     * Возможные значения для отбора по источнику поступления в систему
     */
    const FILTER_PROGRESS_ВСЕ = 1;
    const FILTER_PROGRESS_ВНУТРЕННИЕ = 2;
    const FILTER_PROGRESS_ВНЕШНИЕ = 3;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'tender_id', 'type'], 'integer'],
            [['description'], 'safe'],
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
     * Возвращает массив со значениями для отбора по состояниям проектов.
     * @return array
     */
    public static function fetchFilterProgresses()
    {
        return [
            [
                'id' => self::FILTER_PROGRESS_ВСЕ,
                'name' => 'Все',
            ],
            [
                'id' => self::FILTER_PROGRESS_ВНУТРЕННИЕ,
                'name' => 'Внутренние',
                'hint' => 'Записи в журнал, сделанные системой при работе пользователей с тендером',
            ],
            [
                'id' => self::FILTER_PROGRESS_ВНЕШНИЕ,
                'name' => 'Внешние',
                'hint' => 'Записи, поступившие в систему из внешнего источника (сайта закупок)',
            ],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     * @param array $params
     * @param $route string маршрут для сортировки и постраничного перехода
     * @return ActiveDataProvider
     */
    public function search($params, $route = 'tenders-logs')
    {
        $query = TendersLogs::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => $route,
                'pageSize' => 50,
            ],
            'sort' => [
                //'route' => $route,
                'defaultOrder' => ['created_at' => SORT_DESC, 'id' => SORT_ASC],
                'attributes' => [
                    'id',
                    'created_at',
                    'created_by',
                    'tender_id',
                    'description',
                    'type',
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

        // значения по-умолчанию
        // источник
        if (empty($this->type)) {
            $this->type = self::FILTER_PROGRESS_ВСЕ;
        }

        // дополним условие отбора по источнику
        switch ($this->type) {
            case self::FILTER_PROGRESS_ВНУТРЕННИЕ:
                $query->andWhere([TendersLogs::tableName() . '.type' => TendersLogs::TYPE_ВНУТРЕННЯЯ]);
                break;
            case self::FILTER_PROGRESS_ВНЕШНИЕ:
                $query->andWhere([TendersLogs::tableName() . '.type' => TendersLogs::TYPE_ИСТОЧНИК]);
                break;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'tender_id' => $this->tender_id,
        ]);

        $query->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}
