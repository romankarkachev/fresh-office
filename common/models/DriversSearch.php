<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Drivers;

/**
 * DriversSearch represents the model behind the search form about `common\models\Drivers`.
 */
class DriversSearch extends Drivers
{
    /**
     * Универсальная переменная для поиска по всем полям.
     * @var string
     */
    public $searchEntire;

    /**
     * @var integer поле для отбора по реквизиту ДОПОГ
     */
    public $searchDopog;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'ferryman_id', 'is_deleted', 'state_id', 'has_smartphone', 'is_dopog', 'searchDopog'], 'integer'],
            [['surname', 'name', 'patronymic', 'driver_license', 'dl_issued_at', 'phone', 'pass_serie', 'pass_num', 'pass_issued_at', 'pass_issued_by', 'searchEntire'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ferryman_id' => 'Перевозчик',
            'searchEntire' => 'Универсальный поиск',
            'searchDopog' => 'Есть ДОПОГ',
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
     * Возвращает набор атрибутов, по которым можно сортировать таблицу водителей.
     * @return array
     */
    public static function sortAttributes()
    {
        return [
            'id',
            'ferryman_id',
            'surname',
            'name',
            'patronymic',
            'driver_license',
            'driver_license_index',
            'phone',
            'ferrymanName' => [
                'asc' => ['ferrymen.name' => SORT_ASC],
                'desc' => ['ferrymen.name' => SORT_DESC],
            ],
            'instrCount',
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param $defaultOrder array массив со значениями для сортировки по-умолчанию
     * @param $route string URL для постраничного перехода и сортировки
     * @return ActiveDataProvider
     */
    public function search($params, $defaultOrder=null, $route='ferrymen-drivers')
    {
        if ($defaultOrder == null) $defaultOrder = ['ferrymanName' => SORT_ASC];

        $query = Drivers::find();
        $query->select([
            '*',
            'id' => 'drivers.id',
            'state_id' => 'drivers.state_id',
            'name' => 'drivers.name',
            'phone' => 'drivers.phone',
            'instrCount' => 'instr.count',
            'instrDetails' => 'instr.details',
        ]);

        // LEFT JOIN выполняется быстрее, что подзапрос в SELECT-секции
        // присоединяем количество и список инструктажей
        $query->leftJoin('(
            SELECT
                drivers_instructings.instructed_at,
                drivers_instructings.driver_id,
                COUNT(drivers_instructings.id) AS count,
                GROUP_CONCAT(CONCAT(DATE_FORMAT(drivers_instructings.instructed_at, \'%d.%m.%Y\'), " (",  drivers_instructings.place, ")") ORDER BY drivers_instructings.instructed_at DESC SEPARATOR ", ") AS details
            FROM drivers_instructings
            GROUP BY drivers_instructings.driver_id
        ) AS instr', '`drivers`.`id` = `instr`.`driver_id`');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['route' => $route],
            'sort' => [
                'route' => $route,
                'defaultOrder' => $defaultOrder,
                'attributes' => self::sortAttributes(),
            ]
        ]);

        $this->load($params);
        $query->joinWith(['ferryman']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if (empty($this->searchDopog)) {
            $this->searchDopog = TransportSearch::FILTER_DOPOG_IGNORE;
        }

        // если запрос выполняет перевозчик, то ограничим выборку только по нему
        if (Yii::$app->user->can('ferryman')) {
            $ferryman = Ferrymen::findOne(['user_id' => Yii::$app->user->id]);
            // если связанный перевозчик не будет обнаружен, то вернем пустую выборку
            if ($ferryman == null) {$query->where('1 <> 1'); return $dataProvider;}
            $query->andWhere(['ferryman_id' => $ferryman->id]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'ferryman_id' => $this->ferryman_id,
            'state_id' => $this->state_id,
            'has_smartphone' => $this->has_smartphone,
        ]);

        // для любых пользователей отбор, который невозможно отменить - записи не должны быть помечены на удаление
        if (!Yii::$app->user->can('root')) $query->andWhere(['is_deleted' => false]);

        // дополним текст запроса возможным отбором по полю ДОПОГ
        if (!empty($this->searchDopog)) {
            switch ($this->searchDopog) {
                case TransportSearch::FILTER_DOPOG_YES:
                    $query->andFilterWhere([
                        'is_dopog' => true,
                    ]);
                    break;
                case TransportSearch::FILTER_DOPOG_NO:
                    $query->andFilterWhere([
                        'is_dopog' => false,
                    ]);
                    break;
                case TransportSearch::FILTER_DOPOG_IGNORE:
                    break;
            }
        }

        if ($this->searchEntire != null && $this->searchEntire != '')
            $query->andFilterWhere([
                'or',
                ['like', 'surname', $this->searchEntire],
                ['like', 'drivers.name', $this->searchEntire],
                ['like', 'patronymic', $this->searchEntire],
                ['like', 'driver_license', $this->searchEntire],
                ['like', 'drivers.phone', $this->searchEntire],
                // не уверен, что это нужно:
                //['like', 'pass_serie', $this->searchEntire],
                //['like', 'pass_num', $this->searchEntire],
                //['like', 'pass_issued_by', $this->searchEntire],
            ]);
        else
            $query->andFilterWhere(['like', 'surname', $this->surname])
                ->andFilterWhere(['like', 'drivers.name', $this->name])
                ->andFilterWhere(['like', 'patronymic', $this->patronymic])
                ->andFilterWhere(['like', 'driver_license', $this->driver_license])
                ->andFilterWhere(['like', 'drivers.phone', $this->phone])
                ->andFilterWhere(['like', 'driver_license', $this->driver_license])
                ->andFilterWhere(['like', 'pass_serie', $this->pass_serie])
                ->andFilterWhere(['like', 'pass_num', $this->pass_num])
                ->andFilterWhere(['like', 'pass_issued_by', $this->pass_issued_by]);

        return $dataProvider;
    }
}
