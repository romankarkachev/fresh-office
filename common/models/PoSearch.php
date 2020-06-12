<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Po;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * PoSearch represents the model behind the search form of `common\models\Po`.
 */
class PoSearch extends Po
{
    /**
     * Возможные значения для поля отбора по пометке удаления
     */
    const IS_DELETED_VALUE_NA = 1;
    const IS_DELETED_VALUE_TRUE = 2;
    const IS_DELETED_VALUE_FALSE = 3;

    /**
     * @var integer поле для отбора по пометке удаления
     */
    public $searchDeleted;

    /**
     * @var integer поле для отбора только авансовых отчетов
     */
    public $searchAdvancedReports;

    /**
     * @var string поле отбора, определяющее начало периода даты оплаты
     */
    public $searchPaymentDateStart;

    /**
     * @var string поле отбора, определяющее окончания периода даты оплаты
     */
    public $searchPaymentDateEnd;

    /**
     * @var float поле отбора, определяющее левую границу диапазона сумм
     */
    public $searchAmountStart;

    /**
     * @var float поле отбора, определяющее правую границу диапазона сумм
     */
    public $searchAmountEnd;

    /**
     * @var integer флаг для управления группами статусов при отборе
     */
    public $searchGroupStates;

    /**
     * @var integer поле для отбора по группе статей расходов
     */
    public $searchEiGroup;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'company_id', 'ei_id', 'approved_at', 'paid_at', 'searchDeleted', 'searchAdvancedReports', 'searchGroupStates', 'searchEiGroup'], 'integer'],
            [['amount', 'searchAmountStart', 'searchAmountEnd'], 'number'],
            [['state_id', 'comment', 'searchPaymentDateStart', 'searchPaymentDateEnd'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'searchDeleted' => 'Пометка удаления',
            'searchAdvancedReports' => 'Только авансовые',
            'searchPaymentDateStart' => 'Начало периода',
            'searchPaymentDateEnd' => 'Конец периода',
            'searchAmountStart' => 'Сумма от',
            'searchAmountEnd' => 'Сумма до',
            'searchGroupStates' => 'Группы статусов',
            'searchEiGroup' => 'Группа статей',
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
     * Возвращает массив возможных значения для поля отбора по пометке удаления платежного ордера.
     * @return array
     */
    public static function fetchIsDeletedValues()
    {
        return [
            [
                'id' => self::IS_DELETED_VALUE_NA,
                'name' => 'Не важно',
            ],
            [
                'id' => self::IS_DELETED_VALUE_TRUE,
                'name' => 'Установлена',
            ],
            [
                'id' => self::IS_DELETED_VALUE_FALSE,
                'name' => 'Не помечен',
            ],
        ];
    }

    /**
     * Возвращает массив возможных значения для поля отбора авансовых отчетов.
     * @return array
     */
    public static function fetchIsAdvancedReportValues()
    {
        return [
            [
                'id' => self::IS_DELETED_VALUE_NA,
                'name' => 'Не важно',
            ],
            [
                'id' => self::IS_DELETED_VALUE_TRUE,
                'name' => 'Авансовые',
            ],
            [
                'id' => self::IS_DELETED_VALUE_FALSE,
                'name' => 'Обычные',
            ],
        ];
    }

    /**
     * Делает выборку значений для поля отбора по пометке удаления и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfDeletedForSelect2()
    {
        return ArrayHelper::map(self::fetchIsDeletedValues(), 'id', 'name');
    }

    /**
     * Делает выборку значений для поля отбора авансовых отчетов и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfAdvancedReportsForSelect2()
    {
        return ArrayHelper::map(self::fetchIsAdvancedReportValues(), 'id', 'name');
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param $route string маршрут для сортировки и постраничного перехода
     * @return ActiveDataProvider
     */
    public function search($params, $route = 'po')
    {
        $currentUserId = Yii::$app->user->id;
        $tableName = Po::tableName();
        $joinWith = ['createdByProfile', 'state', 'company', 'ei', 'eiGroup'];
        $query = Po::find()->select([
            $tableName . '.*',
            'id' => $tableName . '.`id`',
        ]);

        if (!Yii::$app->user->can('root')) {
            // всегда для всех, кроме рута, только не помеченные на удаление
            $query->andWhere([$tableName . '.is_deleted' => false]);

            if (Yii::$app->user->can(AuthItem::ROLE_ACCOUNTANT_SALARY)) {
                // для бухгалтеров по зарплате свои условия: только разрешенные в профиле статьи
                $query->andWhere(['ei_id' => UsersEiAccess::find()->select('ei_id')->where(['user_id' => $currentUserId])]);
            }
            else if (!Yii::$app->user->can('accountant_b')) {
                // пользователи могут видеть только свои платежные ордера (поля "Автор" и "Ответственный")
                $query->andWhere([$tableName . '.`created_by`' => $currentUserId]);

                // начальники отделов продаж и экологии - дополнительно своих подчиненных
                $role = '';
                if (Yii::$app->user->can('sales_department_head')) {
                    $role = 'sales_department_manager';
                }
                if (Yii::$app->user->can('ecologist_head')) {
                    $role = 'ecologist';
                }
                if (!empty($role)) {
                    $joinWith[] = 'createdByRoles';
                    $query->orWhere([AuthAssignment::tableName() . '.`item_name`' => $role]);
                }
                unset($role);

                // включаем платежные ордера всех лиц, кто доверил отображение текущему пользователю
                $subQuery = UsersTrusted::find()->select(['user_id'])->where(['section' => UsersTrusted::SECTION_ПО_БЮДЖЕТ, 'trusted_id' => $currentUserId]);
                $query->orWhere([$tableName . '.`created_by`' => $subQuery]);
                unset($subQuery);
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => $route,
                'pageSize' => 50,
            ],
            'sort' => [
                'route' => $route,
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'id' => [
                        'asc' => [$tableName . '.id' => SORT_ASC],
                        'desc' => [$tableName . '.id' => SORT_DESC],
                    ],
                    'created_at',
                    'created_by',
                    'state_id',
                    'company_id',
                    'ei_id',
                    'amount',
                    'approved_at',
                    'paid_at',
                    'comment',
                    'createdByProfileName' => [
                        'asc' => ['createdByProfile.name' => SORT_ASC],
                        'desc' => ['createdByProfile.name' => SORT_DESC],
                    ],
                    'stateName' => [
                        'asc' => [PaymentOrdersStates::tableName() . '.name' => SORT_ASC],
                        'desc' => [PaymentOrdersStates::tableName() . '.name' => SORT_DESC],
                    ],
                    'companyName' => [
                        'asc' => [Companies::tableName() . '.name' => SORT_ASC],
                        'desc' => [Companies::tableName() . '.name' => SORT_DESC],
                    ],
                    'eiName' => [
                        'asc' => [PoEi::tableName() . '.name' => SORT_ASC],
                        'desc' => [PoEi::tableName() . '.name' => SORT_DESC],
                    ],
                    'eigName' => [
                        'asc' => [PoEig::tableName() . '.name' => SORT_ASC],
                        'desc' => [PoEig::tableName() . '.name' => SORT_DESC],
                    ],
                    'eiRepHtml' => [
                        'asc' => [PoEig::tableName() . '.name' => SORT_ASC, PoEi::tableName() . '.name' => SORT_ASC],
                        'desc' => [PoEig::tableName() . '.name' => SORT_DESC, PoEi::tableName() . '.name' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith($joinWith);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // значение по умолчанию для поля пометки удаления
        if (empty($this->searchDeleted)) {
            $this->searchDeleted = self::IS_DELETED_VALUE_NA;
        }

        // дополним текст запроса условием отбора по полю пометки удаления
        if (!empty($this->searchDeleted)) {
            switch ($this->searchDeleted) {
                case self::IS_DELETED_VALUE_TRUE:
                    // только помеченные на удаление
                    $condition = [$tableName . '.`is_deleted`' => true];
                    break;
                case self::IS_DELETED_VALUE_FALSE:
                    // только непомеченные на удаление
                    $condition = [$tableName . '.`is_deleted`' => false];
                    break;
            }
            if (!empty($condition)) {
                $query->andFilterWhere($condition);
            }
            unset($condition);
        }
        else {
            $query->andFilterWhere([
                $tableName . '.`is_deleted`' => $this->is_deleted,
            ]);
        }

        // дополним условием отбора за период по дате оплаты
        if (!empty($this->searchPaymentDateStart) || !empty($this->searchPaymentDateEnd)) {
            if (!empty($this->searchPaymentDateStart) && !empty($this->searchPaymentDateEnd)) {
                // если указаны обе даты
                $query->andFilterWhere(['between', Po::tableName() . '.paid_at', strtotime($this->searchPaymentDateStart . ' 00:00:00'), strtotime($this->searchPaymentDateEnd . ' 23:59:59')]);
            } else if (!empty($this->searchPaymentDateStart) && empty($this->searchPaymentDateEnd)) {
                // если указано только начало периода
                $query->andFilterWhere(['>=', Po::tableName() . '.paid_at', strtotime($this->searchPaymentDateStart . ' 00:00:00')]);
            } else if (empty($this->searchPaymentDateStart) && !empty($this->searchPaymentDateEnd)) {
                // если указан только конец периода
                $query->andFilterWhere(['<=', Po::tableName() . '.paid_at', strtotime($this->searchPaymentDateEnd . ' 23:59:59')]);
            };
        }

        // дополним условием отбора по сумме
        if (!empty($this->searchAmountStart) || !empty($this->searchAmountEnd)) {
            if (!empty($this->searchAmountStart) && !empty($this->searchAmountEnd)) {
                // диапазон указан целиком
                $query->andWhere([
                    'between',
                    Po::tableName() . '.`amount`',
                    $this->searchAmountStart,
                    $this->searchAmountEnd
                ]);
            }
            elseif (!empty($this->searchAmountStart) && empty($this->searchAmountEnd)) {
                // указана только левая граница диапазона, берем все суммы больше или равно
                $query->andWhere(Po::tableName() . '.`amount` >= ' . $this->searchAmountStart);
            }
            elseif (empty($this->searchAmountStart) && !empty($this->searchAmountEnd)) {
                // указана только правая граница диапазона, берем все суммы меньше или равно
                $query->andWhere(Po::tableName() . '.`amount` <= ' . $this->searchAmountEnd);
            }
        }

        if (empty($this->searchGroupStates) && !in_array($this->state_id, PaymentOrdersStates::PAYMENT_STATES_SET_АВАНСОВЫЕ_ОТЧЕТЫ)) {
            if (Yii::$app->user->can('accountant') || Yii::$app->user->can('accountant_b')) {
                $this->searchGroupStates = PaymentOrdersSearch::CLAUSE_STATES_GROUP_ACCOUNTANT_DEFAULT;
            }
            elseif (Yii::$app->user->can('logist') || Yii::$app->user->can(AuthItem::ROLE_ACCOUNTANT_SALARY)) {
                $this->searchGroupStates = PaymentOrdersSearch::CLAUSE_STATES_GROUP_ROOT_LOGIST_ALL;
            }
        }

        if (!empty($this->searchGroupStates)) {
            switch ($this->searchGroupStates) {
                case PaymentOrdersSearch::CLAUSE_STATES_GROUP_ACCOUNTANT_DEFAULT:
                    $query->andWhere(['in', Po::tableName() . '.state_id', PaymentOrdersStates::PAYMENT_STATES_SET_ACCOUNTANT_DEFAULT]);
                    break;
                case PaymentOrdersSearch::CLAUSE_STATES_GROUP_ACCOUNTANT_PAID:
                    $query->andWhere(['in', Po::tableName() . '.state_id', PaymentOrdersStates::PAYMENT_STATES_SET_ACCOUNTANT_PAID]);
                    break;
                case PaymentOrdersSearch::CLAUSE_STATES_GROUP_ACCOUNTANT_ALL:
                    $query->andWhere(['in', Po::tableName() . '.state_id', PaymentOrdersStates::PAYMENT_STATES_SET_ACCOUNTANT_ALL]);
                    break;
                case PaymentOrdersSearch::CLAUSE_STATES_GROUP_ROOT_LOGIST_ALL:
                    // нет условия, все записи кроме авансовых отчетов
                    $query->andWhere(['not in', Po::tableName() . '.state_id', PaymentOrdersStates::PAYMENT_STATES_SET_АВАНСОВЫЕ_ОТЧЕТЫ]);
                    break;
                case PaymentOrdersSearch::CLAUSE_STATES_GROUP_ADVANCE_REPORTS:
                    // только авансовые отчеты
                    $query->andWhere([Po::tableName() . '.state_id' => PaymentOrdersStates::PAYMENT_STATES_SET_АВАНСОВЫЕ_ОТЧЕТЫ]);
                    break;
                default:
                    $query->andWhere([Po::tableName() . '.state_id' => $this->searchGroupStates]);
                    break;
            }
        }
        else {
            $query->andFilterWhere([
                'state_id' => $this->state_id,
            ]);
        }

        // значение по умолчанию для отбора авансовых отчетов
        if (empty($this->searchAdvancedReports)) {
            $this->searchAdvancedReports = self::IS_DELETED_VALUE_NA;
        }

        // дополним текст запроса условием отбора авансовых отчетов
        if (!empty($this->searchAdvancedReports)) {
            $sqAdvancedReports = PoStatesHistory::find()->select('id')->where([
                PoStatesHistory::tableName() . '.`po_id`' => new Expression($tableName . '.`id`'),
                PoStatesHistory::tableName() . '.`state_id`' => PaymentOrdersStates::PAYMENT_STATE_АВАНСОВЫЙ_ОТЧЕТ,
            ])->limit(1);
            switch ($this->searchAdvancedReports) {
                case PoSearch::IS_DELETED_VALUE_NA:
                    // выбран режим "Не важно": просто отмечаем в списке авансовые отчеты специальной пометкой
                    $query->addSelect(['isAdvancedReportInThePast' => $sqAdvancedReports]);
                    break;
                case PoSearch::IS_DELETED_VALUE_TRUE:
                    // выбран режим отбора авансовых отчетов
                    $query->andWhere(['is not', $sqAdvancedReports, null]);
                    break;
                case PoSearch::IS_DELETED_VALUE_FALSE:
                    // выбран режим отбора обычных платежных ордеров, исключая авансовые отчеты
                    $query->andWhere(['is', $sqAdvancedReports, null]);
                    break;
            }
        }

        // дополним текст запроса условием отбора по группе статей
        if (!empty($this->searchEiGroup)) {
            $query->joinWith('eiGroup');
            $query->andFilterWhere([
                'group_id' => $this->searchEiGroup,
            ]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            $tableName . '.id' => $this->id,
            $tableName . '.created_at' => $this->created_at,
            $tableName . '.created_by' => $this->created_by,
            'company_id' => $this->company_id,
            'ei_id' => $this->ei_id,
            'amount' => $this->amount,
            'approved_at' => $this->approved_at,
            'paid_at' => $this->paid_at,
        ]);

        $query->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
