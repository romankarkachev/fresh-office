<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\TransportRequestsDialogs;

/**
 * TransportRequestsDialogsSearch represents the model behind the search form about `common\models\TransportRequestsDialogs`.
 */
class TransportRequestsDialogsSearch extends TransportRequestsDialogs
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'tr_id', 'is_private', 'read_at'], 'integer'],
            [['message'], 'safe'],
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
        $query = TransportRequestsDialogs::find();
        $query->select([
            '*',
            'transport_requests_dialogs.id',
            'roleName' => '(
                SELECT description FROM auth_item
                INNER JOIN auth_assignment aa ON aa.item_name = auth_item.name
                WHERE aa.user_id = transport_requests_dialogs.created_by
            )',
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => '/transport-requests/dialog-messages-list',
            ],
            'sort' => [
                'route' => '/transport-requests/dialog-messages-list',
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'created_at',
                    'createdByName' => [
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
            'tr_id' => $this->tr_id,
            'is_private' => $this->is_private,
            'read_at' => $this->read_at,
        ]);

        $query->andFilterWhere(['like', 'message', $this->message]);

        return $dataProvider;
    }
}
