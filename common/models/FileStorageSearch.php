<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\FileStorage;

/**
 * FileStorageSearch represents the model behind the search form about `common\models\FileStorage`.
 */
class FileStorageSearch extends FileStorage
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'uploaded_at', 'uploaded_by', 'ca_id', 'type_id', 'size'], 'integer'],
            [['ca_name', 'ffp', 'fn', 'ofn'], 'safe'],
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
        $query = FileStorage::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => 'storage',
            ],
            'sort' => [
                'route' => 'storage',
                'defaultOrder' => ['uploaded_at' => SORT_DESC],
                'attributes' => [
                    'id',
                    'uploaded_at',
                    'uploaded_by',
                    'ca_id',
                    'ca_name',
                    'type_id',
                    'ffp',
                    'fn',
                    'ofn',
                    'size',
                    'file',
                    'uploadedByProfileName' => [
                        'asc' => ['profile.name' => SORT_ASC],
                        'desc' => ['profile.name' => SORT_DESC],
                    ],
                    'typeName' => [
                        'asc' => ['uploading_files_meanings.name' => SORT_ASC],
                        'desc' => ['uploading_files_meanings.name' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['type', 'uploadedByProfile']);

        if (!$this->validate() || (empty($this->ca_id))) {
            $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        if (Yii::$app->user->can('logist')) {
            $query->andFilterWhere([
                'type_id' => UploadingFilesMeanings::ТИП_КОНТЕНТА_ТТН,
            ]);
        }
        else {
            $query->andFilterWhere([
                'type_id' => $this->type_id,
            ]);
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'uploaded_at' => $this->uploaded_at,
            'uploaded_by' => $this->uploaded_by,
            'ca_id' => $this->ca_id,
            'size' => $this->size,
        ]);

        $query->andFilterWhere(['like', 'ca_name', $this->ca_name])
            ->andFilterWhere(['like', 'ffp', $this->ffp])
            ->andFilterWhere(['like', 'fn', $this->fn])
            ->andFilterWhere(['like', 'ofn', $this->ofn]);

        return $dataProvider;
    }
}
