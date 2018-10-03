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
     * Возможные значения для отбора по способу создания пакета
     */
    const FILTER_PROGRESS_ALL = 1;
    const FILTER_PROGRESS_DONE = 2;
    const FILTER_PROGRESS_GEHT = 3;

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
            [['id', 'created_at', 'created_by', 'type_id', 'ca_id', 'closed_at', 'searchProgress'], 'integer'],
            [['date_start', 'date_close_plan', 'comment'], 'safe'],
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

        // подзапрос, вычисляющий наименование текущего этапа
        // текущий этап - это первый попавший и только один незавершенный этап
        $sqCurrentMilestoneName = EcoProjectsMilestones::find()
            ->select(EcoMilestones::tableName() . '.name')
            ->joinWith(['milestone'])
            ->where(EcoProjectsMilestones::tableName() . '.project_id = ' . EcoProjects::tableName() . '.id')
            ->andWhere([EcoProjectsMilestones::tableName() . '.closed_at' => null])
            ->limit(1);

        $query = EcoProjects::find()->select([
            '*',
            'id' => \common\models\EcoProjects::tableName() . '.id',
            'currentMilestoneName' => $sqCurrentMilestoneName,
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
                    'id',
                    'created_at',
                    'created_by',
                    'type_id',
                    'ca_id',
                    'date_close_plan',
                    'closed_at',
                    'comment',
                    'createdByProfileName' => [
                        'asc' => ['profile.name' => SORT_ASC],
                        'desc' => ['profile.name' => SORT_DESC],
                    ],
                    'typeName' => [
                        'asc' => ['eco_types.name' => SORT_ASC],
                        'desc' => ['eco_types.name' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['createdByProfile', 'type']);

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
            'type_id' => $this->type_id,
            'ca_id' => $this->ca_id,
            'date_start' => $this->date_start,
            'date_close_plan' => $this->date_close_plan,
            'closed_at' => $this->closed_at,
        ]);

        $query->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
