<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\controllers\EdfController;

/**
 * EdfDialogsSearch represents the model behind the search form of `common\models\EdfDialogs`.
 */
class EdfDialogsSearch extends EdfDialogs
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'ed_id', 'read_at'], 'integer'],
            [['message'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
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
        $query = EdfDialogs::find();
        $query->select([
            '*',
            'edf_dialogs.id',
            'roleName' => '(
                SELECT description FROM auth_item
                INNER JOIN auth_assignment aa ON aa.item_name = auth_item.name
                WHERE aa.user_id = edf_dialogs.created_by
            )',
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => '/' . EdfController::ROOT_URL_FOR_SORT_PAGING . '/' . EdfController::DIALOGS_MESSAGES_LIST_URL,
            ],
            'sort' => [
                'route' => '/' . EdfController::ROOT_URL_FOR_SORT_PAGING . '/' . EdfController::DIALOGS_MESSAGES_LIST_URL,
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'created_at',
                    'createdByProfileName' => [
                        'asc' => ['profile.name' => SORT_ASC],
                        'desc' => ['profile.name' => SORT_DESC],
                    ],
                    'roleName',
                    'message',
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['createdByProfile']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'ed_id' => $this->ed_id,
            'read_at' => $this->read_at,
        ]);

        $query->andFilterWhere(['like', 'message', $this->message]);

        return $dataProvider;
    }
}
