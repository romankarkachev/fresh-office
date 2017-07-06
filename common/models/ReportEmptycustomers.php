<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;

/**
 * ReportEmptycustomers - это отчет по пустым клиентам (нет контактов, проектов, финансов).
 */
class ReportEmptycustomers extends Model
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
            $searchResponsible_condition = '
    AND COMPANY.ID_MANAGER = ' . $this->searchResponsible;

        $query_text = '
SELECT
    COMPANY.ID_COMPANY AS id,
    COMPANY_NAME AS name,
    MANAGERS.MANAGER_NAME AS responsible
FROM CBaseCRM_Fresh_7x.dbo.COMPANY
LEFT JOIN MANAGERS ON MANAGERS.ID_MANAGER = COMPANY.ID_MANAGER

LEFT JOIN (
	SELECT ID_COMPANY, COUNT(ID_LIST_ICQ) AS ICQ_CONTACTS
	FROM LIST_ICQ_NUMBERS
	GROUP BY ID_COMPANY
) AS ICQ ON ICQ.ID_COMPANY = COMPANY.ID_COMPANY
LEFT JOIN (
	SELECT ID_COMPANY, COUNT(ID_LIST_SKYPE) AS SKYPE_CONTACTS
	FROM LIST_SKYPE_NUMBERS
	GROUP BY ID_COMPANY
) AS SKYPE ON SKYPE.ID_COMPANY = COMPANY.ID_COMPANY
LEFT JOIN (
	SELECT ID_COMPANY, COUNT(ID_TELPHONE) AS PHONE_CONTACTS
	FROM LIST_TELEPHONES
	GROUP BY ID_COMPANY
) AS PHONES ON PHONES.ID_COMPANY = COMPANY.ID_COMPANY
LEFT JOIN (
	SELECT COMPANY_ID AS ID_COMPANY, COUNT(EMAIL_ID) AS EMAIL_CONTACTS
	FROM LIST_EMAILS
	GROUP BY COMPANY_ID
) AS EMAILS ON EMAILS.ID_COMPANY = COMPANY.ID_COMPANY
LEFT JOIN (
	SELECT ID_COMPANY, COUNT(ID_CONTACT_MAN) AS COUNT_CONTACTS
	FROM LIST_CONTACT_MAN
	GROUP BY ID_COMPANY
) AS CONTACTS ON CONTACTS.ID_COMPANY = COMPANY.ID_COMPANY

LEFT JOIN (
	SELECT ID_COMPANY, COUNT(ID_MANY) AS COUNT_FINANCE
	FROM LIST_MANYS
	GROUP BY ID_COMPANY
) AS FINANCES ON FINANCES.ID_COMPANY = COMPANY.ID_COMPANY
LEFT JOIN (
	SELECT ID_COMPANY, COUNT(ID_LIST_PROJECT_COMPANY) AS COUNT_PROJECTS
	FROM LIST_PROJECT_COMPANY
	GROUP BY ID_COMPANY
) AS PROJECTS ON PROJECTS.ID_COMPANY = COMPANY.ID_COMPANY
WHERE
    TRASH = 0
    AND ICQ_CONTACTS IS NULL AND SKYPE_CONTACTS IS NULL AND PHONE_CONTACTS IS NULL AND EMAIL_CONTACTS IS NULL AND COUNT_CONTACTS IS NULL
    AND COUNT_FINANCE IS NULL
    AND COUNT_PROJECTS IS NULL' . $searchResponsible_condition;

        $result = Yii::$app->db_mssql->createCommand($query_text)->queryAll();

        $dataProvider = new ArrayDataProvider([
            'modelClass' => 'common\models\ReportEmptycustomers',
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
