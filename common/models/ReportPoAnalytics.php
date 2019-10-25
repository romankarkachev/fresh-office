<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;

/**
 * ReportPoAnalytics - это отчет по бюджетным платежным ордерам.
 */
class ReportPoAnalytics extends Model
{
    /**
     * Названия месяцев на русском, вот такая жизнь
     */
    const MONTHS_RUSSIAN = [
        1 => 'Январь',
        2 => 'Февраль',
        3 => 'Март',
        4 => 'Апрель',
        5 => 'Май',
        6 => 'Июнь',
        7 => 'Июль',
        8 => 'Август',
        9 => 'Сентябрь',
        10 => 'Октябрь',
        11 => 'Ноябрь',
        12 => 'Декабрь',
    ];

    /**
     * @var integer группа статей расходов
     */
    public $searchGroup;

    /**
     * @var integer статья расходов
     */
    public $searchItem;

    /**
     * @var string начало и конец периода для отбора по дате оплаты
     */
    public $searchPaidAtStart;
    public $searchPaidAtEnd;

    /**
     * @var array массив значений свойств для отбора по ним
     */
    public $searchValue;

    /**
     * @var integer количество записей на странице (по-умолчанию - false)
     */
    public $searchPerPage;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'searchGroup', 'searchItem', 'searchPerPage'], 'integer'],
            [['searchPaidAtStart', 'searchPaidAtEnd', 'searchValue'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'searchPaidAtStart' => 'Начало периода',
            'searchPaidAtEnd' => 'Конец периода',
            'searchValue' => 'Значения свойств',
            'searchGroup' => 'Группа статей',
            'searchItem' => 'Статья расходов',
            // для отбора
            'searchPerPage' => 'Записей', // на странице
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
     * @param array $params
     * @return array
     */
    public function search($params)
    {
        // для выборки используется только выбранный год
        $currentYearStart = strtotime(date('Y') . '-01-01');
        $currentYearEnd = strtotime(date('Y') . '-12-31');

        $this->load($params);

        // значения по-умолчанию
        // записей на странице - все
        if (!isset($this->searchPerPage)) $this->searchPerPage = false;

        // выборка статей расходов по группам
        $query = PoEi::find()->select([
            PoEi::tableName() . '.id',
            PoEi::tableName() . '.name',
            'groupId' => PoEig::tableName() . '.id',
            'groupName' => PoEig::tableName() . '.name',
        ])->joinWith(['group'])->orderBy(PoEig::tableName() . '.name, ' . PoEi::tableName() . '.name');

        $query->andFilterWhere([
            PoEig::tableName() . '.id' => $this->searchGroup,
            PoEi::tableName() . '.id' => $this->searchItem,
        ]);

        $ei = $query->asArray()->all();
        unset($query);

        // выборка движений по статьям расходов
        $query = Po::find()->select([
            'ei_id',
            'amount' => 'SUM(`amount`)',
            'period' => 'DATE_FORMAT(FROM_UNIXTIME(`paid_at`), "%m")',
        ])
            ->joinWith(['ei'])
            ->andWhere('`paid_at` IS NOT NULL')
            ->groupBy(['period', 'ei_id']);

        // возможный отбор за период
        if (!empty($this->searchPaidAtStart) || !empty($this->searchPaidAtEnd)) {
            if (!empty($this->searchPaidAtStart) && !empty($this->searchPaidAtEnd)) {
                // если указаны обе даты
                $query->andFilterWhere(['between', Po::tableName() . '.paid_at', $this->searchPaidAtStart . ' 00:00:00', $this->searchPaidAtEnd . ' 23:59:59']);
            }
            elseif (!empty($this->searchPaidAtStart) && empty($this->searchPaidAtEnd)) {
                // если указано только начало периода
                $query->andFilterWhere(['>=', Po::tableName() . '.paid_at', $this->searchPaidAtStart . ' 00:00:00']);
            }
            elseif (empty($this->searchPaidAtStart) && !empty($this->searchPaidAtEnd)) {
                // если указан только конец периода
                $query->andFilterWhere(['<=', Po::tableName() . '.paid_at', $this->searchPaidAtEnd . ' 23:59:59']);
            }
        }
        else {
            $query->andWhere('`paid_at` BETWEEN ' . $currentYearStart . ' AND ' . $currentYearEnd);
        }

        $query->andFilterWhere([
            'group_id' => $this->searchGroup,
            'ei_id' => $this->searchItem,
        ]);

        if (!empty($this->searchValue)) {
            // только так! не перемещать в andFilterWhere, потому что запрос генерируется как
            // SELECT `po_id` FROM `po_pop` WHERE `value_id` IS NULL, а это ошибка
            $query->andWhere([Po::tableName() . '.id' => PoPop::find()->select('po_id')->where(['value_id' => $this->searchValue])]);
        }

        $payments = $query->asArray()->all();

        foreach ($payments as $payment) {
            $key = array_search($payment['ei_id'], array_column($ei, 'id'));
            if (false !== $key) {
                $ei[$key]['amount' . $payment['period']] = $payment['amount'];
            }
        }

        return $ei;
    }
}
