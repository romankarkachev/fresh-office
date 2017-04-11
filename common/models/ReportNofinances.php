<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;

/**
 * ReportNofinances - это отчет по клиентам, с которыми нет финансовых отношений.
 */
class ReportNofinances extends Model
{
    /**
     * Ответственный.
     * @var integer
     */
    public $searchResponsible;

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
            [['id', 'searchResponsible', 'searchPerPage'], 'integer'],
            [['name', 'responsible'], 'string'],
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
            // для отбора
            'searchResponsible' => 'Ответственный',
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
        $searchResponsible_condition = '';
        if (isset($this->searchResponsible))
            $searchResponsible_condition = ' AND COMPANY.ID_MANAGER = ' . $this->searchResponsible;

        $query_text = '
SELECT
    COMPANY.ID_COMPANY AS id,
    COMPANY_NAME AS name,
    MANAGERS.MANAGER_NAME AS responsible
FROM CBaseCRM_Fresh_7x.dbo.COMPANY
LEFT JOIN MANAGERS ON MANAGERS.ID_MANAGER = COMPANY.ID_MANAGER
LEFT JOIN (
	SELECT ID_COMPANY, COUNT(ID_MANY) AS COUNT_FINANCE
	FROM LIST_MANYS
	WHERE ID_SUB_PRIZNAK_MANY = ' . FreshOfficeAPI::FINANCES_PAYMENT_SIGN_УТИЛИЗАЦИЯ . ' AND ID_NAPR = ' . FreshOfficeAPI::FINANCES_DIRECTION_ПРИХОД . '
	GROUP BY ID_COMPANY
) AS FINANCES ON FINANCES.ID_COMPANY = COMPANY.ID_COMPANY
WHERE COUNT_FINANCE IS NULL' . $searchResponsible_condition;

        $result = Yii::$app->db_mssql->createCommand($query_text)->queryAll();

        $dataProvider = new ArrayDataProvider([
            'modelClass' => 'common\models\ReportNofinances',
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
