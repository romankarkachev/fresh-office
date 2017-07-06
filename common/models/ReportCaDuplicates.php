<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;

/**
 * ReportCaDuplicates - это отчет по клиентам, с которыми нет финансовых отношений.
 */
class ReportCaDuplicates extends Model
{
    /**
     * Ответственный.
     * @var integer
     */
    public $searchResponsibleId;

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
            [['id', 'searchResponsibleId', 'searchPerPage'], 'integer'],
            [['name', 'parameter', 'owners'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Параметр',
            'parameter' => 'Значение совпадения',
            'owners' => 'Собственники',
            // для отбора
            'searchResponsibleId' => 'Ответственный',
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
        $result = [];

        $this->load($params);

        // значения по-умолчанию
        // записей на странице - все
        if (!isset($this->searchPerPage)) $this->searchPerPage = false;

        // уточняем условие
        $searchResponsibleId_name_condition = '';
        if ($this->searchResponsibleId != null) {
            $searchResponsibleId_condition = '
    WHERE COMPANY.ID_MANAGER = ' . intval($this->searchResponsibleId);

            $searchResponsibleId_phoneemail_condition = ' AND COMPANY.ID_MANAGER = ' . intval($this->searchResponsibleId);
        }

        // поиск дубликатов по наименованию
        $query_text = '
SELECT
    \'Наименование\' AS name,
    COMPANY_NAME AS parameter,
    \'Количество повторений: \' + CAST(COUNT(*) AS VARCHAR) AS owners
FROM COMPANY' . $searchResponsibleId_name_condition .'
WHERE TRASH = 0
GROUP BY COMPANY_NAME
HAVING COUNT(*) > 1';

        $subresult = Yii::$app->db_mssql->createCommand($query_text)->queryAll();
        $result = ArrayHelper::merge($result, $subresult);

        // поиск дубликатов по номеру телефона
        $query_text = '
SELECT
    \'Номер телефона\' AS name,
    TELEPHONE AS parameter,
    STUFF(
            (
            SELECT \', \' + COMPANY_NAME + \' (\' + CAST(t2.ID_COMPANY AS VARCHAR) + \')\'
            FROM LIST_TELEPHONES t2
            LEFT JOIN COMPANY ON COMPANY.ID_COMPANY = t2.ID_COMPANY
            WHERE t1.TELEPHONE = t2.TELEPHONE
            GROUP BY t2.ID_COMPANY, COMPANY_NAME
            FOR XML PATH(\'\')
            )
    ,1,1,\'\') AS owners
FROM LIST_TELEPHONES t1
LEFT JOIN COMPANY ON COMPANY.ID_COMPANY = t1.ID_COMPANY
WHERE TRASH = 0 AND t1.TELEPHONE IS NOT NULL' . $searchResponsibleId_phoneemail_condition .'
GROUP BY TELEPHONE
HAVING COUNT(DISTINCT t1.ID_COMPANY) > 1';

        $subresult = Yii::$app->db_mssql->createCommand($query_text)->queryAll();
        $result = ArrayHelper::merge($result, $subresult);

        // поиск дубликатов по Email
        $query_text = '
SELECT
    \'E-mail\' AS name,
    EMAIL AS parameter,
    STUFF(
            (
            SELECT \', \' + COMPANY_NAME + \' (\' + CAST(t2.ID_COMPANY AS VARCHAR) + \')\'
            FROM LIST_EMAIL_CLIENT t2
            LEFT JOIN COMPANY ON COMPANY.ID_COMPANY = t2.ID_COMPANY
            WHERE t1.EMAIL = t2.EMAIL
            GROUP BY t2.ID_COMPANY, COMPANY_NAME
            FOR XML PATH(\'\') 
            )
    ,1,1,\'\') AS owners
FROM LIST_EMAIL_CLIENT t1
LEFT JOIN COMPANY ON COMPANY.ID_COMPANY = t1.ID_COMPANY
WHERE TRASH = 0 AND t1.EMAIL IS NOT NULL' . $searchResponsibleId_phoneemail_condition .'
GROUP BY EMAIL
HAVING COUNT(DISTINCT t1.ID_COMPANY) > 1';

        $subresult = Yii::$app->db_mssql->createCommand($query_text)->queryAll();
        $result = ArrayHelper::merge($result, $subresult);

        $dataProvider = new ArrayDataProvider([
            'modelClass' => 'common\models\ReportCaDuplicates',
            'allModels' => $result,
            'pagination' => [
                'pageSize' => $this->searchPerPage,
            ],
            'sort' => [
                'defaultOrder' => ['name' => SORT_ASC],
                'attributes' => [
                    'name',
                    'parameter',
                    'owners',
                ],
            ],
        ]);

        return $dataProvider;
    }
}
