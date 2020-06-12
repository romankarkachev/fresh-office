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
     * Поля для поиска по.
     */
    const DUPLICATING_FIELD_НАИМЕНОВАНИЕ = 1;
    const DUPLICATING_FIELD_ТЕЛЕФОН = 2;
    const DUPLICATING_FIELD_EMAIL = 3;

    /**
     * @var bool признак, позволяющий отбирать только тех контргагентов, которые не помечены пользователем
     */
    public $searchTrueDuplicates;

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
            [['id', 'searchTrueDuplicates', 'searchResponsibleId', 'searchPerPage'], 'integer'],
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
            'searchTrueDuplicates' => 'Исключая отмеченные',
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
     * Возвращает массив допустимых полей для поиска по ним одинаковых контрагентов.
     * @return array
     */
    public static function fetchSearchFieldNames()
    {
        return [
            [
                'id' => self::DUPLICATING_FIELD_НАИМЕНОВАНИЕ,
                'name' => 'Наименование',
            ],
            [
                'id' => self::DUPLICATING_FIELD_ТЕЛЕФОН,
                'name' => 'Телефон',
            ],
            [
                'id' => self::DUPLICATING_FIELD_EMAIL,
                'name' => 'E-mail',
            ],
        ];
    }

    /**
     * Возвращает массив разделов учета, где будут произведены изменения, при слиянии карточек контрагентов.
     * @return array
     */
    public static function fetchCompanyReplaceChapters()
    {
        return [
            [
                'active' => true,
                'tableNames' => ['LIST_CONTACT_MAN'],
                'actionRep' => 'Обработка контактных лиц...',
            ],
            [
                'active' => true,
                'tableNames' => ['LIST_ADD_ADDRESS_COMPANY', 'LIST_TELEPHONES', 'LIST_EMAIL_CLIENT', 'LIST_SKYPE_NUMBERS'],
                'actionRep' => 'Обработка контактной информации...',
            ],
            [
                'active' => true,
                'tableNames' => ['LIST_REQUIS_COMPANY'],
                'actionRep' => 'Обработка реквизитов...',
            ],
            [
                'active' => true,
                'tableNames' => ['LIST_CURATOR_COMPANY'],
                'actionRep' => 'Обработка кураторов...',
            ],
            [
                'active' => true,
                'tableNames' => ['LIST_CONTACT_COMPANY'],
                'actionRep' => 'Обработка задач...',
            ],
            [
                'active' => true,
                'tableNames' => ['LIST_PROJECT_COMPANY'],
                'actionRep' => 'Обработка проектов...',
            ],
            [
                'active' => true,
                'tableNames' => ['LIST_DEAL'],
                'actionRep' => 'Обработка сделок...',
            ],
            [
                'active' => true,
                'tableNames' => ['LIST_MANYS'],
                'actionRep' => 'Обработка финансов...',
            ],
            [
                'active' => true,
                'tableNames' => ['LIST_DOCUMENTS'],
                'actionRep' => 'Обработка документов...',
            ],
            [
                'active' => true,
                'tableNames' => ['LIST_FAVORITES_COMPANY', 'LIST_GOODS_COMPANY', 'LIST_PRIM_COMPANY'],
                'actionRep' => 'Обработка прочей информации...',
            ],
            [
                'active' => false,
                'tableNames' => ['DRIVE_COMPANY'],
                'actionRep' => 'Без понятия что это',
            ],
            [
                'active' => false,
                'tableNames' => ['LIST_COMPANY_EMAILS'],
                'actionRep' => 'Предположительно переписка по компании',
            ],
        ];
    }

    /**
     * Возвращает наименование поля для поиска по нему одинаковых контрагентов.
     * @param $field_id integer идентификатор поля
     * @return string
     */
    public static function getSearchFieldName($field_id)
    {
        $sourceTable = self::fetchSearchFieldNames();
        $key = array_search($field_id, array_column($sourceTable, 'id'));
        if (false !== $key) return $sourceTable[$key]['name'];

        return '<не определено>';
    }

    /**
     * @param $field string
     * @param $value string
     * @return \yii\data\ArrayDataProvider
     */
    public function searchDuplicates($field, $value)
    {
        $models = null;

        switch ($field) {
            case self::DUPLICATING_FIELD_НАИМЕНОВАНИЕ:
                // поиск дубликатов по наименованию
                $models = foCompany::find()->select([
                    'id' => 'COMPANY.ID_COMPANY',
                    'name' => 'COMPANY_NAME',
                    'managerName' => 'MANAGER_NAME',
                ])->distinct()->joinWith('manager')->where(['COMPANY_NAME' => $value])->asArray()->all();
                break;
            case self::DUPLICATING_FIELD_ТЕЛЕФОН:
                // поиск дубликатов по номеру телефона
                $models = foListPhones::find()->select([
                    'id' => 'LIST_TELEPHONES.ID_COMPANY',
                    'name' => 'COMPANY_NAME',
                    'managerName' => 'MANAGER_NAME',
                ])->distinct()->joinWith(['company', 'manager'])->where(['TELEPHONE' => $value])->asArray()->all();
                break;
            case self::DUPLICATING_FIELD_EMAIL:
                // поиск дубликатов по Email
                $models = foListEmailClient::find()->select([
                    'id' => 'LIST_EMAIL_CLIENT.ID_COMPANY',
                    'name' => 'COMPANY_NAME',
                    'managerName' => 'MANAGER_NAME',
                ])->distinct()->joinWith(['company', 'manager'])->where(['email' => $value])->asArray()->all();
                break;
        }

        return new ArrayDataProvider([
            'key' => 'id',
            'allModels' => $models,
            'pagination' => false,
        ]);
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

        // отбор только не помеченных пользователем контрагентов
        if (!isset($this->searchTrueDuplicates)) $this->searchTrueDuplicates = true;

        $conditionTrueDuplicates = '';
        if (!empty($this->searchTrueDuplicates)) {
            $ignoreIds = FoCaDi::find()->column();
            if (!empty($ignoreIds)) {
                $conditionTrueDuplicates = ' AND COMPANY.ID_COMPANY NOT IN (' . implode(',', $ignoreIds) . ')';
            }
            unset($ignoreIds);
        }

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
  ' . self::DUPLICATING_FIELD_НАИМЕНОВАНИЕ . ' AS field,
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
' . self::DUPLICATING_FIELD_ТЕЛЕФОН . ' AS field,
    \'Номер телефона\' AS name,
    TELEPHONE AS parameter,
    STUFF(
            (
            SELECT \', \' + COMPANY_NAME + \' (\' + CAST(t2.ID_COMPANY AS VARCHAR) + \')\'
            FROM LIST_TELEPHONES t2
            LEFT JOIN COMPANY ON COMPANY.ID_COMPANY = t2.ID_COMPANY
            WHERE t1.TELEPHONE = t2.TELEPHONE' . $conditionTrueDuplicates . '
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
    ' . self::DUPLICATING_FIELD_EMAIL . ' AS field,
    \'E-mail\' AS name,
    EMAIL AS parameter,
    STUFF(
            (
            SELECT \', \' + COMPANY_NAME + \' (\' + CAST(t2.ID_COMPANY AS VARCHAR) + \')\'
            FROM LIST_EMAIL_CLIENT t2
            LEFT JOIN COMPANY ON COMPANY.ID_COMPANY = t2.ID_COMPANY
            WHERE t1.EMAIL = t2.EMAIL' . $conditionTrueDuplicates . '
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

        // извлечем идентификаторы каждой строки и поместим их в специальное поле
        foreach ($result as $index => $row) {
            if (empty($row['owners'])) {
                unset($result[$index]);
                continue;
            }

            $ids = [];
            $cas = explode(', ', $row['owners']);
            if (count($cas) <= 20) {
                // слишком большие объемы не интересуют
                foreach ($cas as $ca) {
                    preg_match('#\(([0-9]*[.]?[0-9]+)\)#', $ca, $m);
                    if (!empty($m) && is_numeric($m[1])) {
                        $ids[] = $m[1];
                    }
                }
            }
            else {
                $ids[] = 'пометка';
            }
            $result[$index]['ids'] = implode(',', $ids);
            $result[$index]['idsc'] = implode(', ', $ids);
        }

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
