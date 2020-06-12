<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\CorrespondencePackages;
use yii\helpers\ArrayHelper;

/**
 * CorrespondencePackagesSearch represents the model behind the search form about `common\models\CorrespondencePackages`.
 */
class CorrespondencePackagesSearch extends CorrespondencePackages
{
    /**
     * Группы типов проектов
     */
    const CLAUSE_STATE_DEFAULT = 1; // по умолчанию
    const CLAUSE_STATE_PROCESS = 2; // только в работе
    const CLAUSE_STATE_JUST_CREATED = 3; // только формирующиеся пакеты
    const CLAUSE_STATE_READY = 4; // только ожидают отправки
    const CLAUSE_STATE_SENT = 5; // только отправленные
    const CLAUSE_STATE_DELIVERED = 6; // только доставленные
    const CLAUSE_STATE_FINISHED = 7; // только завершенные
    const CLAUSE_STATE_ALL = 8; // все статусы
    const CLAUSE_STATE_UNCLAIMED = 9; // невостребовано

    /**
     * Возможные значения для отбора по способу создания пакета
     */
    const FILTER_PACKAGE_TYPE_ALL = 1;
    const FILTER_PACKAGE_TYPE_MANUAL = 2;
    const FILTER_PACKAGE_TYPE_AUTO = 3;

    /**
     * @var integer значение отбора по способу создания пакетов корреспонденции
     */
    public $searchPackageType;

    /**
     * Флаг для управления статусами в выборке.
     * @var integer
     */
    public $searchGroupProjectStates;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'is_manual', 'cps_id', 'ready_at', 'sent_at', 'delivered_at', 'paid_at', 'fo_project_id', 'fo_id_company', 'state_id', 'type_id', 'pd_id', 'address_id', 'manager_id', 'fo_contact_id', 'rejects_count', 'searchPackageType', 'searchGroupProjectStates'], 'integer'],
            [['customer_name', 'pad', 'track_num', 'other', 'comment', 'contact_person'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'searchPackageType' => 'Способ создания',
            'searchGroupProjectStates' => 'Группы статусов',
            'searchCreatedFrom' => 'Период с',
            'searchCreatedTo' => 'Период по',
            'searchPerPage' => 'Записей', // на странице
        ]);
    }

    /**
     * Возвращает массив со значениями для отбора по способу создания пакета.
     * @return array
     */
    public static function fetchFilterPackagesTypes()
    {
        return [
            [
                'id' => self::FILTER_PACKAGE_TYPE_ALL,
                'name' => 'Все',
            ],
            [
                'id' => self::FILTER_PACKAGE_TYPE_MANUAL,
                'name' => 'Ручные',
                'hint' => 'Только созданные старшими операторами вручную',
            ],
            [
                'id' => self::FILTER_PACKAGE_TYPE_AUTO,
                'name' => 'Авто',
                'hint' => 'Импортированные из CRM автоматически по расписанию',
            ],
        ];
    }

    /**
     * Возвращает массив со значениями для отбора по статусу пакета корреспонденции.
     * @return array
     */
    public static function fetchFilterCps()
    {
        return [
            [
                'id' => CorrespondencePackagesStates::STATE_СОГЛАСОВАНИЕ,
                'name' => 'Согласование',
            ],
            [
                'id' => self::FILTER_PACKAGE_TYPE_MANUAL,
                'name' => 'Ручные',
                'hint' => 'Только созданные старшими операторами вручную',
            ],
            [
                'id' => self::FILTER_PACKAGE_TYPE_AUTO,
                'name' => 'Авто',
                'hint' => 'Импортированные из CRM автоматически по расписанию',
            ],
        ];
    }

    /**
     * Возвращает массив с идентификаторами статусов проектов по группам.
     * @return array
     */
    public static function fetchGroupProjectStatesIds()
    {
        $result = [
            [
                'id' => self::CLAUSE_STATE_JUST_CREATED,
                'name' => 'Формирование',
                'hint' => 'Формирование документов на отправку',
            ],
            [
                'id' => self::CLAUSE_STATE_READY,
                'name' => 'Ожидает отправки',
            ],
            [
                'id' => self::CLAUSE_STATE_SENT,
                'name' => 'Отправленные',
            ],
            [
                'id' => self::CLAUSE_STATE_DELIVERED,
                'name' => 'Доставленные',
            ],
            [
                'id' => self::CLAUSE_STATE_FINISHED,
                'name' => 'Завершенные',
            ],
            [
                'id' => self::CLAUSE_STATE_UNCLAIMED,
                'name' => 'Невостребованные',
            ],
            [
                'id' => self::CLAUSE_STATE_ALL,
                'name' => 'Все',
            ],
        ];

        if (!Yii::$app->user->can('operator_head')) {
            $result = ArrayHelper::merge([
                [
                    'id' => self::CLAUSE_STATE_DEFAULT,
                    'name' => 'По умолчанию',
                    'hint' => 'Формирование документов на отправку + Ожидает отправки',
                ],
                [
                    'id' => self::CLAUSE_STATE_PROCESS,
                    'name' => 'В работе',
                    'hint' => 'Все, кроме доставленных и завершенных',
                ],
            ], $result);
        }

        return $result;
    }

    /**
     * Возвращает массив с идентификаторами статусов проектов по группам (для менеджера).
     * @return array
     */
    public static function fetchGroupProjectStatesIdsForManager()
    {
        return [
            [
                'id' => self::CLAUSE_STATE_READY,
                'name' => 'Ожидает отправки',
            ],
            [
                'id' => self::CLAUSE_STATE_SENT,
                'name' => 'Отправленные',
            ],
            [
                'id' => self::CLAUSE_STATE_DELIVERED,
                'name' => 'Доставленные',
            ],
            [
                'id' => self::CLAUSE_STATE_ALL,
                'name' => 'Все',
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
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $tableName = CorrespondencePackages::tableName();
        $query = CorrespondencePackages::find()->select([
            '*',
            'id' => $tableName . '.id',
            'created_at' => $tableName . '.created_at',
            'state_id' => $tableName . '.state_id',
            'manager_id' => $tableName . '.manager_id',
        ]);

        if (Yii::$app->user->can('sales_department_head') || Yii::$app->user->can('sales_department_manager') || Yii::$app->user->can('ecologist') || Yii::$app->user->can('ecologist_head')) {
            // пользователи могут видеть только свои пакеты (поле "Ответственный")
            $query->where([$tableName . '.`manager_id`' => Yii::$app->user->id]);
            // начальники отделов продаж и экологии - дополнительно своих подчиненных
            if (Yii::$app->user->can('sales_department_head')) {
                $query->joinWith(['managerRole']);
                $query->orWhere(['item_name' => 'sales_department_manager']);
            }
            if (Yii::$app->user->can('ecologist_head')) {
                $query->joinWith(['managerRole']);
                $query->orWhere(['item_name' => 'ecologist']);
            }
            // также в выборку включаются пакеты всех лиц, кто доверил отображение текущему пользователю
            $query->orWhere([$tableName . '.`manager_id`' => UsersTrusted::find()->select(['user_id'])->where(['section' => UsersTrusted::SECTION_ПАКЕТЫ_КОРРЕСПОНДЕНЦИИ, 'trusted_id' => Yii::$app->user->id])]);
        }

        if (Yii::$app->user->can('dpc_head')) {
            $query->andWhere([
                'is_manual' => false,
            ]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => 'correspondence-packages',
                'pageSize' => 100,
            ],
            'sort' => [
                'route' => 'correspondence-packages',
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'id',
                    'created_at' => [
                        'asc' => [$tableName . '.created_at' => SORT_ASC],
                        'desc' => [$tableName . '.created_at' => SORT_DESC],
                    ],
                    'ready_at',
                    'sent_at',
                    'delivered_at',
                    'fo_project_id',
                    'customer_name',
                    'state_id' => [
                        'asc' => ['correspondence_packages.state_id' => SORT_ASC],
                        'desc' => ['correspondence_packages.state_id' => SORT_DESC],
                    ],
                    'type_id',
                    'pad',
                    'pd_id',
                    'track_num',
                    'other',
                    'comment',
                    'fo_contact_id',
                    'contact_person',
                    'rejects_count',
                    'cpsName' => [
                        'asc' => ['correspondence_packages_states.name' => SORT_ASC],
                        'desc' => ['correspondence_packages_states.name' => SORT_DESC],
                    ],
                    'stateName' => [
                        'asc' => ['projects_states.name' => SORT_ASC],
                        'desc' => ['projects_states.name' => SORT_DESC],
                    ],
                    'typeName' => [
                        'asc' => ['projects_types.name' => SORT_ASC],
                        'desc' => ['projects_types.name' => SORT_DESC],
                    ],
                    'pdName' => [
                        'asc' => ['post_delivery_kinds.name' => SORT_ASC],
                        'desc' => ['post_delivery_kinds.name' => SORT_DESC],
                    ],
                    'managerProfileName' => [
                        'asc' => ['managerProfile.name' => SORT_ASC],
                        'desc' => ['managerProfile.name' => SORT_DESC],
                    ],
                ]
            ]
        ]);

        $this->load($params);
        $query->joinWith(['cps', 'state', 'type', 'pd', 'managerProfile', 'edf', 'lastCpsChange']);

        // по умолчанию все видят пакеты документов только в статусах "Формирование документов на отправку" и "Ожидает отправки"
        if ($this->searchGroupProjectStates == null) {
            if (Yii::$app->user->can('sales_department_manager'))
                // для менеджеров значение по-умолчанию - Все
                $this->searchGroupProjectStates = self::CLAUSE_STATE_ALL;
            else
                $this->searchGroupProjectStates = self::CLAUSE_STATE_DEFAULT;
        }

        // по-умолчанию отображаются все пакеты вне зависимости от того, каким способом они были созданы (вручную или автоматически)
        if ($this->searchPackageType == null) {
            $this->searchPackageType = self::FILTER_PACKAGE_TYPE_ALL;
        }

        // дополним условием отбора по статусам
        switch ($this->searchGroupProjectStates) {
            case self::CLAUSE_STATE_DEFAULT:
                $query->andWhere(['in', $tableName . '.state_id', [ProjectsStates::STATE_ФОРМИРОВАНИЕ_ДОКУМЕНТОВ_НА_ОТПРАВКУ, ProjectsStates::STATE_ОЖИДАЕТ_ОТПРАВКИ]]);
                break;
            case self::CLAUSE_STATE_PROCESS:
                $query->andWhere(['not in', $tableName . '.state_id', [ProjectsStates::STATE_ДОСТАВЛЕНО, ProjectsStates::STATE_ЗАВЕРШЕНО]]);
                break;
            case self::CLAUSE_STATE_SENT:
                $query->andWhere([$tableName . '.state_id' => ProjectsStates::STATE_ОТПРАВЛЕНО]);
                break;
            case self::CLAUSE_STATE_JUST_CREATED:
                $query->andWhere([$tableName . '.state_id' => ProjectsStates::STATE_ФОРМИРОВАНИЕ_ДОКУМЕНТОВ_НА_ОТПРАВКУ]);
                break;
            case self::CLAUSE_STATE_READY:
                $query->andWhere([$tableName . '.state_id' => ProjectsStates::STATE_ОЖИДАЕТ_ОТПРАВКИ]);
                break;
            case self::CLAUSE_STATE_DELIVERED:
                $query->andWhere([$tableName . '.state_id' => ProjectsStates::STATE_ДОСТАВЛЕНО]);
                break;
            case self::CLAUSE_STATE_UNCLAIMED:
                $query->andWhere([$tableName . '.state_id' => ProjectsStates::STATE_НЕВОСТРЕБОВАНО]);
                break;
            case self::CLAUSE_STATE_FINISHED:
                $query->andWhere([$tableName . '.state_id' => ProjectsStates::STATE_ЗАВЕРШЕНО]);
                break;
        }

        // дополним условие отбора по способу создания
        switch ($this->searchPackageType) {
            case self::FILTER_PACKAGE_TYPE_MANUAL:
                $query->andWhere(['is_manual' => true]);
                break;
            case self::FILTER_PACKAGE_TYPE_AUTO:
                $query->andWhere(['is_manual' => false]);
                break;
        }

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            $tableName . '.id' => $this->id,
            $tableName . '.created_at' => $this->created_at,
            'is_manual' => $this->is_manual,
            'cps_id' => $this->cps_id,
            'ready_at' => $this->ready_at,
            'sent_at' => $this->sent_at,
            'delivered_at' => $this->delivered_at,
            'paid_at' => $this->paid_at,
            'fo_project_id' => $this->fo_project_id,
            'fo_id_company' => $this->fo_id_company,
            'correspondence_packages.state_id' => $this->state_id,
            'type_id' => $this->type_id,
            'pd_id' => $this->pd_id,
            'address_id' => $this->address_id,
            $tableName . '.manager_id' => $this->manager_id,
            'fo_contact_id' => $this->fo_contact_id,
            'rejects_count' => $this->rejects_count,
        ]);

        $query->andFilterWhere(['like', 'customer_name', $this->customer_name])
            ->andFilterWhere(['like', 'pad', $this->pad])
            ->andFilterWhere(['like', 'track_num', $this->track_num])
            ->andFilterWhere(['like', 'other', $this->other])
            ->andFilterWhere(['like', 'comment', $this->comment])
            ->andFilterWhere(['like', 'contact_person', $this->contact_person]);

        return $dataProvider;
    }
}
