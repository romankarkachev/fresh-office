<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\EcoProjects;
use yii\helpers\ArrayHelper;

/**
 * EcoProjectsSearch represents the model behind the search form about `common\models\EcoProjects`.
 */
class EcoProjectsSearch extends EcoProjects
{
    /**
     * Возможные значения для отбора по состояниям прогресса проектов
     */
    const FILTER_PROGRESS_ALL = 1;
    const FILTER_PROGRESS_DONE = 2;
    const FILTER_PROGRESS_GEHT = 3;
    const FILTER_PROGRESS_EXPIRED_ONLY = 4;

    /**
     * @var integer значение отбора по состояниям прогресса проектов
     */
    public $searchProgress;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'responsible_id', 'type_id', 'ca_id', 'closed_at', 'searchProgress'], 'integer'],
            [['contract_amount'], 'number'],
            [['date_start', 'date_finish_contract', 'date_close_plan', 'comment'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'searchProgress' => 'Состояние',
        ]);
    }

    /**
     * Возвращает массив со значениями для отбора по состояниям проектов.
     * @return array
     */
    public static function fetchFilterProgresses()
    {
        return [
            [
                'id' => self::FILTER_PROGRESS_ALL,
                'name' => 'Все',
            ],
            [
                'id' => self::FILTER_PROGRESS_GEHT,
                'name' => 'Выполняются',
                'hint' => 'Только те проекты, по которым в настоящий момент еще идет работа',
            ],
            [
                'id' => self::FILTER_PROGRESS_DONE,
                'name' => 'Завершенные',
                'hint' => 'Проекты, по которым пройдены и завершены все этапы',
            ],
            [
                'id' => self::FILTER_PROGRESS_EXPIRED_ONLY,
                'name' => 'Только просроченные',
                'hint' => 'Проекты, текущий этап в которых просрочен по собственному сроку',
            ],
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
     * @param $route string URL для перехода в список записей
     * @return ActiveDataProvider
     */
    public function search($params, $route = 'eco-projects')
    {
        // подзапрос, где вычисляется общее количество этапов в проекте
        $sqTotalMilestonesCount = EcoProjectsMilestones::find()
            ->select('COUNT(*)')
            ->where(EcoProjectsMilestones::tableName() . '.project_id = ' . EcoProjects::tableName() . '.id');

        // подзапрос, который вычисляет количество уже выполненных этапов
        $sqMilestonesDoneCount = EcoProjectsMilestones::find()
            ->select('COUNT(*)')
            ->where(EcoProjectsMilestones::tableName() . '.project_id = ' . EcoProjects::tableName() . '.id')
            ->andWhere(EcoProjectsMilestones::tableName() . '.closed_at IS NOT NULL');

        // подзапрос, который определяет имя текущего этапа
        $sqCurrentMilestoneName = EcoProjectsMilestones::find()
            ->select('name')
            ->joinWith(['milestone'])
            ->where(EcoProjectsMilestones::tableName() . '.project_id = ' . EcoProjects::tableName() . '.id')
            ->andWhere([EcoProjectsMilestones::tableName() . '.closed_at' => null])
            ->orderBy('order_no')->limit(1);

        // подзапрос, который определяет срок выполнения текущего этапа
        $slCurrentMilestoneDatePlan = EcoProjectsMilestones::find()
            ->select('date_close_plan')
            ->joinWith(['milestone'])
            ->where(EcoProjectsMilestones::tableName() . '.project_id = ' . EcoProjects::tableName() . '.id')
            ->andWhere([EcoProjectsMilestones::tableName() . '.closed_at' => null])
            ->orderBy('order_no')->limit(1);

        $query = EcoProjects::find()->select([
            'id' => EcoProjects::tableName() . '.id',
            'created_at',
            'created_by',
            'responsible_id',
            'type_id',
            'ca_id',
            'contract_amount',
            'date_start',
            'date_finish_contract',
            'date_close_plan' => EcoProjects::tableName() . '.date_close_plan',
            'closed_at' => EcoProjects::tableName() . '.closed_at',
            'comment',
            /*
            // можно использовать такие поля при выборке через LEFT JOIN
            'currentMilestoneName' => 'currentMilestone.name',
            'currentMilestoneDatePlan' => 'currentMilestone.date_close_plan',
            */
            'currentMilestoneName' => $sqCurrentMilestoneName,
            'currentMilestoneDatePlan' => $slCurrentMilestoneDatePlan,
            'milestonesDoneCount' => $sqMilestonesDoneCount,
            'totalMilestonesCount' => $sqTotalMilestonesCount,
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => $route,
            ],
            'sort' => [
                'route' => $route,
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'id' => [
                        'asc' => [EcoProjects::tableName() . '.id' => SORT_ASC],
                        'desc' => [EcoProjects::tableName() . '.id' => SORT_DESC],
                    ],
                    'created_at',
                    'created_by',
                    'responsible_id',
                    'type_id',
                    'ca_id',
                    'contract_amount',
                    'date_start',
                    'date_finish_contract',
                    'date_close_plan',
                    'closed_at',
                    'comment',
                    'createdByProfileName' => [
                        'asc' => ['createdByProfile.name' => SORT_ASC],
                        'desc' => ['createdByProfile.name' => SORT_DESC],
                    ],
                    'responsibleProfileName' => [
                        'asc' => ['responsibleProfile.name' => SORT_ASC],
                        'desc' => ['responsibleProfile.name' => SORT_DESC],
                    ],
                    'typeName' => [
                        'asc' => ['eco_types.name' => SORT_ASC],
                        'desc' => ['eco_types.name' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['createdByProfile', 'responsibleProfile', 'type']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // значения по-умолчанию
        // состояния проектов
        if (empty($this->searchProgress)) {
            $this->searchProgress = self::FILTER_PROGRESS_ALL;
        }

        // дополним условие отбора по состояниям проектов
        switch ($this->searchProgress) {
            case self::FILTER_PROGRESS_DONE:
                $query->andWhere(EcoProjects::tableName() . '.closed_at IS NOT NULL');
                break;
            case self::FILTER_PROGRESS_GEHT:
                $query->andWhere([EcoProjects::tableName() . '.closed_at' => null]);
                break;
            case self::FILTER_PROGRESS_EXPIRED_ONLY:
                // подзапрос, вычисляющий наименование текущего этапа
                // текущий этап - это первый попавшийся незавершенный этап
                $query->leftJoin(['currentMilestone' => EcoProjectsMilestones::find()
                    ->select([
                        'project_id',
                        'name',
                        'date_close_plan',
                    ])
                    ->joinWith(['milestone'])
                    ->where([EcoProjectsMilestones::tableName() . '.closed_at' => null])
                    ->groupBy('project_id')
                    ->orderBy('order_no')
                ], '`currentMilestone`.`project_id` = `' . EcoProjects::tableName() . '`.`id`');

                $query->andWhere('currentMilestone.date_close_plan <= NOW()');
                break;
        }

        if (!Yii::$app->user->can('root') && !Yii::$app->user->can('ecologist_head')) {
            // для эколога отбор только по его собственным записям при помощи подзапроса
            $query->andWhere([
                EcoProjects::tableName() . '.id' => EcoProjectsAccess::find()->select('project_id')->where(['user_id' => Yii::$app->user->id])
            ]);
        }
        else {
            $query->andFilterWhere([
                EcoProjects::tableName() . '.id' => $this->id,
            ]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'responsible_id' => $this->responsible_id,
            'type_id' => $this->type_id,
            'ca_id' => $this->ca_id,
            'contract_amount' => $this->contract_amount,
            'date_start' => $this->date_start,
            'date_finish_contract' => $this->date_finish_contract,
            'date_close_plan' => $this->date_close_plan,
            'closed_at' => $this->closed_at,
        ]);

        $query->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
