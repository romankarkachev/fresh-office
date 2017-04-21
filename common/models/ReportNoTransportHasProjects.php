<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;

/**
 * ReportNoTransportHasProjects - это отчет по клиентам, которые имеют проекты и записи в финансах с признаком
 * утилизация, но без транспорта.
 */
class ReportNoTransportHasProjects extends Model
{
    /**
     * Дата начала периода.
     * @var string
     */
    public $searchPeriodStart;

    /**
     * Дата окончания периода.
     * @var string
     */
    public $searchPeriodEnd;

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
            [['id', 'searchPerPage'], 'integer'],
            [['name', 'responsible'], 'string'],
            [['searchPeriodStart', 'searchPeriodEnd'], 'safe'],
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
            'responsible' => 'Ответственный',
            // для сортировки
            'searchPeriodStart' => 'Начало периода',
            'searchPeriodEnd' => 'Конец периода',
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
     * @return \yii\data\ArrayDataProvider
     */
    public function search($params)
    {
        $this->load($params);

        // значения по-умолчанию
        // записей на странице - все
        if (!isset($this->searchPerPage)) $this->searchPerPage = false;

        // уточняется запрос (добавляется условие, если оно есть)
        $searchPeriod_condition = '';
        if ($this->searchPeriodStart !== null or $this->searchPeriodEnd !== null)
            if ($this->searchPeriodStart !== '' && $this->searchPeriodEnd !== '') {
                // если указаны обе даты
                $searchPeriod_condition = ' AND DATE_MANY BETWEEN CONVERT(datetime, \'' . $this->searchPeriodStart . ' 00:00:00' . '\', 120) AND CONVERT(datetime, \'' . $this->searchPeriodEnd . ' 23:59:59' . '\', 120)';
            }
            else if ($this->searchPeriodStart !== '' && $this->searchPeriodEnd === '') {
                // если указан только начало периода
                $searchPeriod_condition = 'DATE_MANY >= CONVERT(datetime, \'' . $this->searchPeriodStart . ' 00:00:00' . '\', 120)';
            }
            else if ($this->searchPeriodStart === '' && $this->searchPeriodEnd !== '') {
                // если указан только конец периода
                $searchPeriod_condition = 'DATE_MANY <= CONVERT(datetime, \'' . $this->searchPeriodEnd . ' 23:59:59' . '\', 120)';
            };

        $query_text = '
SELECT COMPANY.ID_COMPANY AS id, COMPANY_NAME AS name, MANAGERS.MANAGER_NAME AS responsible, ISNULL(U.MESAURE, 0) AS UTILIZATION, T.MESAURE
FROM COMPANY
LEFT JOIN (
	SELECT ID_COMPANY, COUNT(ID_MANY) AS MESAURE
	FROM LIST_MANYS
	WHERE ID_SUB_PRIZNAK_MANY = ' . FreshOfficeAPI::FINANCES_PAYMENT_SIGN_УТИЛИЗАЦИЯ . ' AND ID_NAPR = ' . FreshOfficeAPI::FINANCES_DIRECTION_ПРИХОД . $searchPeriod_condition . '
	GROUP BY ID_COMPANY
) AS U ON U.ID_COMPANY = COMPANY.ID_COMPANY
LEFT JOIN (
	SELECT ID_COMPANY, COUNT(ID_MANY) AS MESAURE
	FROM LIST_MANYS
	WHERE ID_SUB_PRIZNAK_MANY = ' . FreshOfficeAPI::FINANCES_PAYMENT_SIGN_ТРАНСПОРТ . ' AND ID_NAPR = ' . FreshOfficeAPI::FINANCES_DIRECTION_ПРИХОД . $searchPeriod_condition . '
	GROUP BY ID_COMPANY
) AS T ON T.ID_COMPANY = COMPANY.ID_COMPANY
LEFT JOIN (
	SELECT ID_COMPANY, COUNT(ID_LIST_PROJECT_COMPANY) AS MESAURE
	FROM LIST_PROJECT_COMPANY
	GROUP BY ID_COMPANY
) AS P ON P.ID_COMPANY = COMPANY.ID_COMPANY
LEFT JOIN MANAGERS ON MANAGERS.ID_MANAGER = COMPANY.ID_MANAGER
WHERE U.MESAURE IS NOT NULL AND T.MESAURE IS NULL AND P.MESAURE IS NOT NULL';

        $result = Yii::$app->db_mssql->createCommand($query_text)->queryAll();

        $dataProvider = new ArrayDataProvider([
            'modelClass' => 'common\models\ReportNoTransportHasProjects',
            'allModels' => $result,
            'key' => 'id', // поле, которое заменяет primary key
            'pagination' => [
                'pageSize' => $this->searchPerPage,
            ],
            'sort' => [
                'defaultOrder' => ['name' => SORT_ASC],
                'attributes' => [
                    'id',
                    'name',
                    'responsible',
                ],
            ],
        ]);

        return $dataProvider;
    }
}
