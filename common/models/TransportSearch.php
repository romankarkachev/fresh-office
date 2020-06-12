<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Transport;
use yii\helpers\ArrayHelper;

/**
 * TransportSearch represents the model behind the search form about `common\models\Transport`.
 */
class TransportSearch extends Transport
{
    /**
     * Возможные значения для поля отбора "ДОПОГ"
     */
    const FILTER_DOPOG_YES = 1;
    const FILTER_DOPOG_NO = 2;
    const FILTER_DOPOG_IGNORE = 3;

    /**
     * @var string поле для поиска по всем полям
     */
    public $searchEntire;

    /**
     * @var string способы погрузки через запятую для отбора по ним
     */
    public $searchLoadTypes;

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
            [['id', 'ferryman_id', 'is_deleted', 'state_id', 'tt_id', 'brand_id', 'is_dopog', 'searchDopog'], 'integer'],
            [['vin', 'rn', 'trailer_rn', 'comment', 'searchEntire', 'searchLoadTypes'], 'safe'],
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
            'searchLoadTypes' => 'Способы погрузки',
            'searchDopog' => 'Есть ДОПОГ',
        ];
    }

    /**
     * Возвращает массив с возможными значениями для поля, позволяющего выполнять отбор по наличию ДОПОГ.
     * @return array
     */
    public static function fetchDopog()
    {
        return [
            [
                'id' => self::FILTER_DOPOG_YES,
                'name' => 'Есть допуск',
            ],
            [
                'id' => self::FILTER_DOPOG_NO,
                'name' => 'Допуска нет',
            ],
            [
                'id' => self::FILTER_DOPOG_IGNORE,
                'name' => 'Не имеет значения',
            ],
        ];
    }

    /**
     * Делает выборку вариантов отбора по полю ДОПОГ и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfDopogForSelect2()
    {
        return ArrayHelper::map(self::fetchDopog(), 'id', 'name');
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
     * Возвращает набор атрибутов, по которым можно сортировать таблицу транспорта.
     * @return array
     */
    public static function sortAttributes()
    {
        return [
            'id',
            'created_at' => [
                'asc' => ['transport.created_at' => SORT_ASC],
                'desc' => ['transport.created_at' => SORT_DESC],
            ],
            'created_by' => [
                'asc' => ['transport.created_by' => SORT_ASC],
                'desc' => ['transport.created_by' => SORT_DESC],
            ],
            'updated_at' => [
                'asc' => ['transport.updated_at' => SORT_ASC],
                'desc' => ['transport.updated_at' => SORT_DESC],
            ],
            'updated_by' => [
                'asc' => ['transport.updated_by' => SORT_ASC],
                'desc' => ['transport.updated_by' => SORT_DESC],
            ],
            'vin',
            'ferryman_id',
            'tt_id',
            'brand_id',
            'rn',
            'trailer_rn',
            'comment',
            'ferrymanName' => [
                'asc' => ['ferrymen.name' => SORT_ASC],
                'desc' => ['ferrymen.name' => SORT_DESC],
            ],
            'ttName' => [
                'asc' => ['transport_types.name' => SORT_ASC],
                'desc' => ['transport_types.name' => SORT_DESC],
            ],
            'brandName' => [
                'asc' => ['transport_brands.name' => SORT_ASC],
                'desc' => ['transport_brands.name' => SORT_DESC],
            ],
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
    public function search($params, $defaultOrder=null, $route='ferrymen-transport')
    {
        if ($defaultOrder == null) $defaultOrder = ['ferrymanName' => SORT_ASC];

        $query = Transport::find();
        $query->select([
            '*',
            'id' => 'transport.id',
            'state_id' => 'transport.state_id',
            'inspCount' => 'insp.count',
            'inspDetails' => 'insp.details',
        ]);

        // LEFT JOIN выполняется быстрее, что подзапрос в SELECT-секции
        // присоединяем количество и список инструктажей
        $query->leftJoin('(
            SELECT
                transport_inspections.inspected_at,
                transport_inspections.transport_id,
                COUNT(transport_inspections.id) AS count,
                GROUP_CONCAT(CONCAT(DATE_FORMAT(transport_inspections.inspected_at, \'%d.%m.%Y\'), " (",  transport_inspections.place, ")") ORDER BY transport_inspections.inspected_at DESC SEPARATOR ", ") AS details
            FROM transport_inspections
            GROUP BY transport_inspections.transport_id
        ) AS insp', '`transport`.`id` = `insp`.`transport_id`');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['route' => $route],
            'sort' => [
                'route' => $route,
                'defaultOrder' => $defaultOrder,
                'attributes' => self::sortAttributes(),
            ],
        ]);

        $this->load($params);
        $query->joinWith(['ferryman', 'tt', 'brand']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if (empty($this->searchDopog)) {
            $this->searchDopog = self::FILTER_DOPOG_IGNORE;
        }

        // если запрос выполняет перевозчик, то ограничим выборку только по нему
        if (Yii::$app->user->can('ferryman')) {
            $ferryman = Ferrymen::findOne(['user_id' => Yii::$app->user->id]);
            // если связанный перевозчик не будет обнаружен, то вернем пустую выборку
            if ($ferryman == null) {$query->where('1 <> 1'); return $dataProvider;}
            $query->andWhere(['ferryman_id' => $ferryman->id]);
        }

        // отбор по способу погрузки (если выбрано несколько способов, то применяется принцип "любой из выбранных")
        if (!empty($this->searchLoadTypes)) {
            $query->andFilterWhere([
                'transport.id' => TransportLoadTypes::find()->select('transport_id')->where(['lt_id' => $this->searchLoadTypes]),
            ]);
        }
        else {
            $query->andFilterWhere([
                'transport.id' => $this->id,
            ]);
        }
        // grid filtering conditions
        $query->andFilterWhere([
            'ferryman_id' => $this->ferryman_id,
            'state_id' => $this->state_id,
            'tt_id' => $this->tt_id,
            'brand_id' => $this->brand_id,
        ]);

        // для любых пользователей отбор, который невозможно отменить - записи не должны быть помечены на удаление
        if (!Yii::$app->user->can('root')) $query->andWhere(['is_deleted' => false]);

        // дополним текст запроса возможным отбором по полю ДОПОГ
        if (!empty($this->searchDopog)) {
            switch ($this->searchDopog) {
                case self::FILTER_DOPOG_YES:
                    $query->andFilterWhere([
                        'is_dopog' => true,
                    ]);
                    break;
                case self::FILTER_DOPOG_NO:
                    $query->andFilterWhere([
                        'is_dopog' => false,
                    ]);
                    break;
                case self::FILTER_DOPOG_IGNORE:
                    break;
            }
        }

        if ($this->searchEntire != null && $this->searchEntire != '')
            $query->andFilterWhere([
                'or',
                ['like', 'vin', $this->searchEntire],
                ['like', 'rn', $this->searchEntire],
                ['like', 'trailer_rn', $this->searchEntire],
                ['like', 'transport_types.name', $this->searchEntire],
            ]);
        else
            $query->andFilterWhere(['like', 'vin', $this->vin])
                ->andFilterWhere(['like', 'rn', $this->rn])
                ->andFilterWhere(['like', 'trailer_rn', $this->trailer_rn])
                ->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
