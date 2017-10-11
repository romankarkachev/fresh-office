<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\LicensesFiles;

/**
 * LicensesFilesSearch represents the model behind the search form about `common\models\LicensesFiles`.
 */
class LicensesFilesSearch extends LicensesFiles
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'uploaded_at', 'uploaded_by', 'organization_id', 'size'], 'integer'],
            [['ffp', 'fn', 'ofn'], 'safe'],
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
        $query = LicensesFiles::find();
        $query->select([
            '*',
            'id' => 'licenses_files.id',
            'fkkos' => 'fkkos.details',
        ]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => 'licenses-files',
            ],
            'sort' => [
                'route' => 'licenses-files',
                'defaultOrder' => ['uploaded_at' => SORT_DESC],
                'attributes' => [
                    'id',
                    'uploaded_at',
                    'uploaded_by',
                    'organization_id',
                    'ofn',
                    'organizationName' => [
                        'asc' => ['organizations.name' => SORT_ASC],
                        'desc' => ['organizations.name' => SORT_DESC],
                    ],
                    'fkkos',
                ],
            ],
        ]);

        // LEFT JOIN выполняется быстрее, чем подзапрос в SELECT-секции
        // присоединяем количество документов
        $query->leftJoin('(
            SELECT
                licenses_fkko_pages.file_id,
                COUNT(licenses_fkko_pages.id) AS count,
                GROUP_CONCAT(CONCAT(fkko.fkko_code, " <em class=\"text-muted\">(", fkko.fkko_name, ")</em>") SEPARATOR ", ") AS details
            FROM licenses_fkko_pages
            LEFT JOIN fkko on fkko.id = licenses_fkko_pages.fkko_id
            GROUP BY licenses_fkko_pages.file_id
        ) AS fkkos', '`fkkos`.`file_id` = `licenses_files`.`id`');

        $this->load($params);
        $query->joinWith(['organization']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'uploaded_at' => $this->uploaded_at,
            'uploaded_by' => $this->uploaded_by,
            'organization_id' => $this->organization_id,
            'size' => $this->size,
        ]);

        $query->andFilterWhere(['like', 'ffp', $this->ffp])
            ->andFilterWhere(['like', 'fn', $this->fn])
            ->andFilterWhere(['like', 'ofn', $this->ofn]);

        return $dataProvider;
    }
}
