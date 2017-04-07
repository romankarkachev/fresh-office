<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;

/**
 * Report1 represents the model behind the search form about `common\models\Documents`.
 */
class Report1 extends Model
{
    /**
     * Признак оплаты "Утилизация".
     */
    const FO_PAYMENT_SIGN_UTILIZATION = 1;

    /**
     * Признак оплаты "Транспорт".
     */
    const FO_PAYMENT_SIGN_TRANSPORT = 2;

    public $id;
    public $first_payment;

    /**
     * Дата, финансовые документы менее которой будут использованы при отборе.
     * По-умолчанию - год назад от текущей даты и ранее.
     * @var date
     */
    public $searchPeriod;

    /**
     * Признак оплаты.
     * По-умолчанию - Утилизация.
     * @var integer
     */
    public $searchPaymentSign;

    /**
     * Условие для суммы оборотов - больше (меньше) или равно.
     * @var string
     */
    public $searchSumCondition;

    /**
     * Сумма оборотов.
     * По-умолчанию - 30 млн.
     * @var float
     */
    public $searchSumLimit;

    /**
     * Количество записей на странице.
     * По-умолчанию - false.
     * @var integer
     */
    public $searchPerPage;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'searchPaymentSign', 'searchPerPage'], 'integer'],
            [['turnover', 'searchSumLimit'], 'number'],
            [['name', 'reliable'], 'string'],
            [['first_payment', 'searchPeriod', 'searchSumCondition'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование',
            'reliable' => 'Ответственный',
            'first_payment' => 'Первая оплата',
            'turnover' => 'Оборот',
            // для сортировки
            'searchPeriod' => 'Дата (<=)',
            'searchPaymentSign' => 'Признак оплаты',
            'searchSumCondition' => 'Условие',
            'searchSumLimit' => 'Оборот',
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
     *
     * @param array $params
     *
     * @return \yii\data\ActiveDataProvider
     */
    public function search($params)
    {
        $this->load($params);

        // значения по-умолчанию
        // период - год назад от текущей даты в формате yyyy-mm-dd
        if (!isset($this->searchPeriod)) $this->searchPeriod = date('Y-m-d', strtotime(date('Y-m-d').' -1 year'));
        // признак оплаты - Утилизация
        if (!isset($this->searchPaymentSign)) $this->searchPaymentSign = Report1::FO_PAYMENT_SIGN_UTILIZATION;
        // условие для суммы оборотов - меньше или равно
        if (!isset($this->searchSumCondition)) $this->searchSumCondition = '<=';
        // оборот с клиентом - не менее 30 млн
        if (!isset($this->searchSumLimit)) $this->searchSumLimit = 30000000;
        // записей на странице - все
        if (!isset($this->searchPerPage)) $this->searchPerPage = false;

        $query_text = '
SELECT COMPANY.ID_COMPANY AS id, COMPANY_NAME AS name, MANAGERS.MANAGER_NAME AS reliable, FINANCES.DATE_MANY AS first_payment, LEAVINGS.TURNOVER AS turnover
FROM COMPANY
LEFT JOIN (
	SELECT ID_COMPANY, MIN(DATE_MANY) AS DATE_MANY
	FROM LIST_MANYS
	WHERE ID_SUB_PRIZNAK_MANY = ' . $this->searchPaymentSign . '
	GROUP BY ID_COMPANY
) AS FINANCES ON FINANCES.ID_COMPANY = COMPANY.ID_COMPANY
LEFT JOIN (
	SELECT ID_COMPANY, SUM(CASE WHEN ID_NAPR = 2 THEN -SUM_RUB ELSE SUM_RUB END) AS TURNOVER
	FROM LIST_MANYS
	WHERE ID_SUB_PRIZNAK_MANY = ' . $this->searchPaymentSign . '
	GROUP BY ID_COMPANY
) AS LEAVINGS ON LEAVINGS.ID_COMPANY = COMPANY.ID_COMPANY
LEFT JOIN MANAGERS ON MANAGERS.ID_MANAGER = COMPANY.ID_MANAGER
WHERE DATE_MANY <= CONVERT(datetime, \'' . $this->searchPeriod . '\', 120) AND turnover ' . $this->searchSumCondition . ' ' . $this->searchSumLimit . '';

        $result = Yii::$app->db_mssql->createCommand($query_text)->queryAll();

        $dataProvider = new ArrayDataProvider([
            'modelClass' => 'common\models\Report1',
            'allModels' => $result,
            'pagination' => [
                'pageSize' => $this->searchPerPage,
            ],
            'sort' => [
                'defaultOrder' => ['name' => SORT_ASC],
                'attributes' => [
                    'id',
                    'name',
                    'reliable',
                    'first_payment',
                    'turnover',
                ],
            ],
        ]);

        return $dataProvider;
    }

    /**
     * Возвращает в виде массива разновидности признаков оплаты.
     * @return array
     */
    public static function fetchPaymentSigns()
    {
        $query_text = 'SELECT ID_SUB_PRIZNAK_MANY AS id, DISCRIPTION_SUB_MANY AS name FROM CBaseCRM_Fresh_7x.dbo.SUB_PRIZNAK_MANY';

        return Yii::$app->db_mssql->createCommand($query_text)->queryAll();
    }

    /**
     * Возвращает в виде массива разновидности условий для суммы оборотов.
     * @return array
     */
    public static function fetchSumConditions()
    {
        return [
            [
                'id' => '<=',
                'name' => '<=',
            ],
            [
                'id' => '>=',
                'name' => '>=',
            ],
        ];
    }

    /**
     * Делает выборку признаков оплаты и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfPaymentSignsForSelect2()
    {
        return ArrayHelper::map(self::fetchPaymentSigns() , 'id', 'name');
    }

    /**
     * Делает выборку условий для суммы оборотов и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapOfSumConditionsForSelect2()
    {
        return ArrayHelper::map(self::fetchSumConditions() , 'id', 'name');
    }
}
