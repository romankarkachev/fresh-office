<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\TransportRequests;
use yii\helpers\ArrayHelper;

/**
 * TransportRequestsSearch represents the model behind the search form about `common\models\TransportRequests`.
 */
class TransportRequestsSearch extends TransportRequests
{
    /**
     * Поле отбора, определяющее начало периода даты движения.
     * @var string
     */
    public $searchDateStart;

    /**
     * Поле отбора, определяющее окончания периода даты движения.
     * @var string
     */
    public $searchDateEnd;

    /**
     * Поле для отбора по отходу (fkko_id).
     * @var integer
     */
    public $searchFkko;

    /**
     * Поле для отбора по отходу (fkko_name).
     * @var integer
     */
    public $searchFkkoName;

    /**
     * Поле для отбора по типу техники.
     * @var integer
     */
    public $searchTransportType;

    /**
     * Поле для отбора только избранных запросов.
     * @var integer
     */
    public $searchOnlyFavorite;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'finished_at', 'finished_by', 'customer_id', 'region_id', 'city_id', 'state_id', 'our_loading', 'periodicity_id', 'spec_free', 'searchFkko', 'searchTransportType', 'searchOnlyFavorite'], 'integer'],
            [['customer_name', 'address', 'comment_manager', 'comment_logist', 'special_conditions', 'spec_hose', 'spec_cond', 'searchDateStart', 'searchDateEnd', 'searchFkkoName'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();

        $labels['searchDateStart'] = 'Создан с';
        $labels['searchDateEnd'] = 'По';
        $labels['searchFkko'] = 'Отход';
        $labels['searchFkkoName'] = 'Отход';
        $labels['searchTransportType'] = 'Тип техники';
        $labels['searchOnlyFavorite'] = 'Только избранные';

        return $labels;
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
        $query = TransportRequests::find();

        $select = [
            '*',
            'id' => 'transport_requests.id',
            'tpWasteLinear' => '(
                SELECT GROUP_CONCAT(
                    CONCAT(
                        transport_requests_waste.fkko_name,
                        CASE WHEN transport_requests_waste.measure IS NULL THEN "" ELSE CONCAT(
                            " (",
                            FORMAT(transport_requests_waste.measure, 2, "ru_RU"),
                            CASE WHEN transport_requests_waste.unit_id IS NULL THEN "" ELSE CONCAT(" ", units.name) END,
                            ")"
                        ) END
                    ) SEPARATOR "\n"
                ) FROM transport_requests_waste
                LEFT JOIN units ON units.id = transport_requests_waste.unit_id
                WHERE transport_requests_waste.tr_id = transport_requests.id
            )',
            'tpTransportLinear' => '(
                SELECT GROUP_CONCAT(
                    CONCAT(
                        transport_types.name,
                        CASE WHEN transport_requests_transport.amount IS NULL THEN "<em class=\"text-muted\"> ожидайте</em>" ELSE CONCAT(" - ", FORMAT(transport_requests_transport.amount, 0, "ru_RU"), " р.") END
                    ) SEPARATOR "\n"
                ) FROM transport_types
                LEFT JOIN transport_requests_transport ON transport_requests_transport.tt_id = transport_types.id
                WHERE transport_requests_transport.tr_id = transport_requests.id
            )',
        ];
        $current_role = Yii::$app->authManager->getRolesByUser(Yii::$app->user->id);
        if (is_array($current_role)) {
            $roleName = array_shift($current_role)->name;
            $select['unreadMessagesCount'] = '(
                SELECT COUNT(transport_requests_dialogs.id) FROM `transport_requests_dialogs`
                LEFT JOIN auth_assignment ON auth_assignment.user_id = transport_requests_dialogs.created_by
                LEFT JOIN auth_item ON auth_item.name = auth_assignment.item_name
                WHERE
                    auth_assignment.item_name <> "' . $roleName . '"
                    AND tr_id=transport_requests.id
                    AND is_private = ' . TransportRequestsDialogs::DIALOGS_PUBLIC . '
                    AND read_at IS NULL
            )';
            $select['unreadPrivateMessagesCount'] = '(
                SELECT COUNT(transport_requests_dialogs.id) FROM `transport_requests_dialogs`
                LEFT JOIN auth_assignment ON auth_assignment.user_id = transport_requests_dialogs.created_by
                LEFT JOIN auth_item ON auth_item.name = auth_assignment.item_name
                WHERE
                    auth_assignment.item_name <> "' . $roleName . '"
                    AND tr_id=transport_requests.id
                    AND is_private = ' . TransportRequestsDialogs::DIALOGS_PRIVATE . '
                    AND read_at IS NULL
            )';
        }
        else {
            $select['unreadMessagesCount'] = 0;
            $select['unreadPrivateMessagesCount'] = 0;
        }

        $query->select($select);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => 'transport-requests',
                'pageSize' => 50,
            ],
            'sort' => [
                'route' => 'transport-requests',
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'id',
                    'created_at',
                    'created_by',
                    'finished_at',
                    'customer_id',
                    'customer_name',
                    'region_id',
                    'city_id',
                    'address',
                    'state_id',
                    'comment_manager',
                    'comment_logist',
                    'our_loading',
                    'periodicity_id',
                    'special_conditions',
                    'spec_free',
                    'spec_hose',
                    'spec_cond',
                    'createdByName' => [
                        'asc' => ['profile.name' => SORT_ASC],
                        'desc' => ['profile.name' => SORT_DESC],
                    ],
                    'finishedByProfileName' => [
                        'asc' => ['finishedByProfile.name' => SORT_ASC],
                        'desc' => ['finishedByProfile.name' => SORT_DESC],
                    ],
                    'regionName' => [
                        'asc' => ['region.name' => SORT_ASC],
                        'desc' => ['region.name' => SORT_DESC],
                    ],
                    'cityName' => [
                        'asc' => ['city.name' => SORT_ASC],
                        'desc' => ['city.name' => SORT_DESC],
                    ],
                    'stateName' => [
                        'asc' => ['transport_requests_states.name' => SORT_ASC],
                        'desc' => ['transport_requests_states.name' => SORT_DESC],
                    ],
                    'periodicityName' => [
                        'asc' => ['periodicity_kinds.name' => SORT_ASC],
                        'desc' => ['periodicity_kinds.name' => SORT_DESC],
                    ],
                    'tpWasteLinear',
                    'tpTransportLinear',
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['createdByProfile', 'finishedByProfile', 'region', 'city', 'state', 'periodicity']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if ($this->searchFkkoName != null || $this->searchTransportType != null) {
            $subquery = [];
            $subquery2 = [];
            if ($this->searchFkkoName != null) {
                // отбор по наименованию отхода, дополним условием основной запрос
                $subquery = TransportRequestsWaste::find()
                    ->select(['id' => 'tr_id'])
                    ->andFilterWhere(['like', 'fkko_name', $this->searchFkkoName])
                    ->asArray()
                    ->column();
            }

            if ($this->searchTransportType != null) {
                // отбор по наименованию отхода, дополним условием основной запрос
                $subquery2 = TransportRequestsTransport::find()
                    ->select(['id' => 'tr_id'])
                    ->andFilterWhere(['tt_id' => $this->searchTransportType])
                    ->asArray()
                    ->column();
            }

            if ($this->searchFkkoName != null && $this->searchTransportType != null)
                // если задано и то, и то, то массив объединяется только одинаковыми идентификаторами
                $condition = array_intersect($subquery, $subquery2);
            else
                // иначе просто совмещаются
                $condition = ArrayHelper::merge($subquery, $subquery2);

            $query->andWhere(['in', 'transport_requests.id', $condition]);
        }
        else
            $query->andFilterWhere([
                'transport_requests.id' => $this->id,
            ]);

        if ($this->searchDateStart !== null || $this->searchDateEnd !== null) {
            if ($this->searchDateStart !== '' && $this->searchDateEnd !== '') {
                // если указаны обе даты
                $query->andFilterWhere(['between', 'transport_requests.created_at', strtotime($this->searchDateStart . ' 00:00:00'), strtotime($this->searchDateEnd . ' 23:59:59')]);
            } else if ($this->searchDateStart !== '' && $this->searchDateEnd === '') {
                // если указано только начало периода
                $query->andFilterWhere(['>=', 'transport_requests.created_at', strtotime($this->searchDateStart . ' 00:00:00')]);
            } else if ($this->searchDateStart === '' && $this->searchDateEnd !== '') {
                // если указан только конец периода
                $query->andFilterWhere(['<=', 'transport_requests.created_at', strtotime($this->searchDateEnd . ' 23:59:59')]);
            };
        }
        else
            $query->andFilterWhere([
                'created_at' => $this->created_at,
            ]);

        // дополним запрос условием по отметке "Избранное"
        if (!empty($this->searchOnlyFavorite)) {
            $query->andFilterWhere([
                'is_favorite' => true,
            ]);
        }

        $query->andFilterWhere([
            parent::tableName() . '.created_by' => $this->created_by,
            parent::tableName() . '.finished_at' => $this->finished_at,
            parent::tableName() . '.finished_by' => $this->finished_by,
            'customer_id' => $this->customer_id,
            'transport_requests.region_id' => $this->region_id,
            'city_id' => $this->city_id,
            'transport_requests.state_id' => $this->state_id,
            'our_loading' => $this->our_loading,
            'periodicity_id' => $this->periodicity_id,
            'spec_free' => $this->spec_free,
        ]);

        $query->andFilterWhere(['like', 'customer_name', $this->customer_name])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'comment_manager', $this->comment_manager])
            ->andFilterWhere(['like', 'comment_logist', $this->comment_logist])
            ->andFilterWhere(['like', 'special_conditions', $this->special_conditions])
            ->andFilterWhere(['like', 'spec_hose', $this->spec_hose])
            ->andFilterWhere(['like', 'spec_cond', $this->spec_cond]);

        return $dataProvider;
    }
}
