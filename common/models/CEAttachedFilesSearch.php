<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\CEAttachedFiles;

/**
 * CEAttachedFilesSearch represents the model behind the search form about `common\models\CEAttachedFiles`.
 */
class CEAttachedFilesSearch extends CEAttachedFiles
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'message_id', 'size'], 'integer'],
            [['ofn'], 'safe'],
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
        $query = CEAttachedFiles::find()->groupBy(['ofn', 'size']);
        $query->select([
            'lettersIds' => 'GROUP_CONCAT(ce_attached_files.message_id SEPARATOR ", ")',
            'ofn',
            'size',
        ]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => 'attached-files',
                'pageSize' => 50,
            ],
            'sort' => [
                'route' => 'attached-files',
                'defaultOrder' => ['size' => SORT_DESC],
                'attributes' => [
                    'id',
                    'message_id',
                    'ofn',
                    'size',
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'message_id' => $this->message_id,
            'size' => $this->size,
        ]);

        $query->andFilterWhere(['like', 'ofn', $this->ofn]);

        return $dataProvider;
    }
}
