<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Отчет показывает контрагентов, в которых куратором назначен выбранный пользователь.
 */
class ReportCompaniesCurator extends Model
{
    /**
     * @var integer ответственный
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
            ['searchResponsible', 'required'],
            [['searchResponsible', 'searchPerPage'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
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
     * @return \yii\data\ActiveDataProvider
     */
    public function search($params)
    {
        $query = foCompanyCurator::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $this->searchPerPage,
            ],
            'sort' => [
                'defaultOrder' => ['companyName' => SORT_ASC],
                'attributes' => [
                    'ID_COMPANY',
                    'companyName' => [
                        'asc' => [foCompany::tableName() . '.COMPANY_NAME' => SORT_ASC],
                        'desc' => [foCompany::tableName() . '.COMPANY_NAME' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith('company');

        // значения по-умолчанию
        // записей на странице - все
        if (isset($this->searchPerPage)) {
            $dataProvider->pagination->pageSize = $this->searchPerPage;
        }
        else {
            $dataProvider->pagination->pageSize = false;
        }

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            foCompanyCurator::tableName() . '.ID_MANAGER' => $this->searchResponsible,
        ]);

        $query->andWhere([
            'or',
            [foCompany::tableName() . '.TRASH' => null],
            [foCompany::tableName() . '.TRASH' => 0],
        ]);

        return $dataProvider;
    }
}
