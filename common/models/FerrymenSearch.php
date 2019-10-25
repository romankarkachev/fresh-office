<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Ferrymen;
use yii\helpers\ArrayHelper;

/**
 * FerrymenSearch represents the model behind the search form about `common\models\Ferrymen`.
 */
class FerrymenSearch extends Ferrymen
{
    /**
     * @var string универсальная переменная для поиска по всем полям
     */
    public $searchEntire;

    /**
     * @var integer поле для отбора по типу транспорта
     */
    public $searchTransportType;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'updated_at', 'updated_by', 'fo_id', 'opfh_id', 'tax_kind', 'ft_id', 'pc_id', 'state_id', 'notify_when_payment_orders_created', 'user_id', 'ppdq', 'searchTransportType'], 'integer'],
            [['name', 'name_crm', 'name_full', 'name_short', 'inn', 'kpp', 'ogrn', 'address_j', 'address_f', 'phone', 'email', 'contact_person', 'post', 'phone_dir', 'email_dir', 'contact_person_dir', 'post_dir', 'ati_code', 'contract_expires_at', 'searchEntire'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'searchEntire' => 'Универсальный поиск',
            'searchTransportType' => 'Тип транспорта',
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
        $query = Ferrymen::find();
        $query->select([
            '*',
            'id' => 'ferrymen.id',
            'name' => 'ferrymen.name',
            'driversCount' => 'ferrymanDrivers.count',
            'driversDetails' => 'ferrymanDrivers.details',
            'transportCount' => 'ferrymanTransport.count',
            'transportDetails' => 'ferrymanTransport.details',
        ]);

        // LEFT JOIN выполняется быстрее, чем подзапрос в SELECT-секции
        // присоединяем количество и состав водителей
        $query->leftJoin('(
            SELECT
                drivers.ferryman_id,
                COUNT(drivers.id) AS count,
                GROUP_CONCAT(CONCAT(drivers.surname, " ", drivers.name, " ", drivers.patronymic) SEPARATOR ", ") AS details
            FROM drivers
            GROUP BY drivers.ferryman_id
        ) AS ferrymanDrivers', '`ferrymen`.`id` = `ferrymanDrivers`.`ferryman_id`');

        $query->leftJoin('(
            SELECT
                transport.ferryman_id,
                COUNT(transport.id) AS count,
                GROUP_CONCAT(CONCAT(transport.vin, " ", transport.rn, " ", transport.trailer_rn) SEPARATOR ", ") AS details
            FROM transport
            GROUP BY transport.ferryman_id
        ) AS ferrymanTransport', '`ferrymen`.`id` = `ferrymanTransport`.`ferryman_id`');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => 'ferrymen',
            ],
            'sort' => [
                'route' => 'ferrymen',
                'defaultOrder' => ['name' => SORT_ASC],
                'attributes' => [
                    'id',
                    'name' => [
                        'asc' => ['ferrymen.name' => SORT_ASC],
                        'desc' => ['ferrymen.name' => SORT_DESC],
                    ],
                    'ft_id',
                    'pc_id',
                    'phone',
                    'email',
                    'contact_person',
                    'contact_person_dir',
                    'ftName' => [
                        'asc' => ['ferrymen_types.name' => SORT_ASC],
                        'desc' => ['ferrymen_types.name' => SORT_DESC],
                    ],
                    'pcName' => [
                        'asc' => ['payment_conditions.name' => SORT_ASC],
                        'desc' => ['payment_conditions.name' => SORT_DESC],
                    ],
                    'driversCount',
                    'transportCount',
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['ft', 'pc']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // возможный отбор по типу транспорта
        if (!empty($this->searchTransportType)) {
            $query->leftJoin(['stt' => (new \yii\db\Query())->select([
                'ferryman_id',
                'sttCount' => 'COUNT(`tt_id`)',
            ])->from(Transport::tableName())->andWhere([
                'tt_id' => $this->searchTransportType,
            ])->groupBy(['ferryman_id'])], 'stt.ferryman_id = ' . new \yii\db\Expression('`' . Ferrymen::tableName() . '`.`id`'));
            $query->andWhere('`stt`.`sttCount` IS NOT NULL AND `stt`.`sttCount` > 0');
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
            'fo_id' => $this->fo_id,
            'opfh_id' => $this->opfh_id,
            'tax_kind' => $this->tax_kind,
            'ft_id' => $this->ft_id,
            'pc_id' => $this->pc_id,
            'state_id' => $this->state_id,
            'contract_expires_at' => $this->contract_expires_at,
            'notify_when_payment_orders_created' => $this->notify_when_payment_orders_created,
            'user_id' => $this->user_id,
            'ppdq' => $this->ppdq,
        ]);

        if (!empty($this->searchEntire != null))
            $query->andFilterWhere([
                'or',
                ['like', 'ferrymen.name', $this->searchEntire],
                ['like', 'ferrymen.phone', $this->searchEntire],
                ['like', 'ferrymen.email', $this->searchEntire],
                ['like', 'contact_person', $this->searchEntire],
                ['like', 'inn', $this->searchEntire],
            ]);
        else
            $query->andFilterWhere(['like', 'ferrymen.name', $this->name])
                ->andFilterWhere(['like', 'ferrymen.name_crm', $this->name_crm])
                ->andFilterWhere(['like', 'ferrymen.name_full', $this->name_full])
                ->andFilterWhere(['like', 'ferrymen.name_short', $this->name_short])
                ->andFilterWhere(['like', 'ferrymen.inn', $this->inn])
                ->andFilterWhere(['like', 'ferrymen.kpp', $this->kpp])
                ->andFilterWhere(['like', 'ferrymen.ogrn', $this->ogrn])
                ->andFilterWhere(['like', 'ferrymen.address_j', $this->address_j])
                ->andFilterWhere(['like', 'ferrymen.address_f', $this->address_f])
                ->andFilterWhere(['like', 'ferrymen.phone', $this->phone])
                ->andFilterWhere(['like', 'ferrymen.email', $this->email])
                ->andFilterWhere(['like', 'ferrymen.contact_person', $this->contact_person])
                ->andFilterWhere(['like', 'ferrymen.post', $this->post])
                ->andFilterWhere(['like', 'ferrymen.phone_dir', $this->phone_dir])
                ->andFilterWhere(['like', 'ferrymen.email_dir', $this->email_dir])
                ->andFilterWhere(['like', 'ferrymen.contact_person_dir', $this->contact_person_dir])
                ->andFilterWhere(['like', 'ferrymen.post_dir', $this->post_dir])
                ->andFilterWhere(['like', 'ferrymen.ati_code', $this->ati_code]);

        return $dataProvider;
    }
}
