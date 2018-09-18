<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\CEMailboxes;

/**
 * CEMailboxesSearch represents the model behind the search form about `common\models\CEMailboxes`.
 */
class CEMailboxesSearch extends CEMailboxes
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'created_by', 'is_active', 'is_ssl'], 'integer'],
            [['name', 'host', 'username', 'password', 'port'], 'safe'],
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
        $query = CEMailboxes::find()
            /*
            ->select([
            '*',
            'id' => CEMailboxes::tableName() . '.id',
            'name' => CEMailboxes::tableName() . '.name',
            'messagesCount',
            //'messagesCount' => 'messages.count',
            /*
            'messagesCount' => '(
                SELECT COUNT(`id`) FROM `' . CEMessages::tableName() . '`
                WHERE `' . CEMessages::tableName() . '`.`mailbox_id` = `' . CEMailboxes::tableName() . '`.`id` AND `' . CEMessages::tableName() . '`.`is_complete` = true
            )',
            */
        //])
        ;
        //$subQuery = CEMessages::find()->select(['mailbox_id', 'messagesCount' => 'COUNT(`id`)'])->where(['is_complete' => true])->groupBy('mailbox_id');
        //$query->leftJoin(['messages' => $subQuery], '`messagez`.`mailbox_id` = `' . CEMailboxes::tableName() . '`.`id`');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => 'mailboxes',
                'pageSize' => 50,
            ],
            'sort' => [
                'route' => 'mailboxes',
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'id',
                    'created_at',
                    'created_by',
                    'name',
                    'type_id',
                    'category_id',
                    'is_active',
                    'host',
                    'username',
                    'password',
                    'port',
                    'is_ssl',
                    'is_primary_done',
                    'typeName' => [
                        'asc' => ['ce_mailboxes_types.name' => SORT_ASC],
                        'desc' => ['ce_mailboxes_types.name' => SORT_DESC],
                    ],
                    'categoryName' => [
                        'asc' => ['ce_mailboxes_categories.name' => SORT_ASC],
                        'desc' => ['ce_mailboxes_categories.name' => SORT_DESC],
                    ],
                    'messagesCount',
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['type', 'category']);

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
            'is_active' => $this->is_active,
            'is_ssl' => $this->is_ssl,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'host', $this->host])
            ->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'password', $this->password])
            ->andFilterWhere(['like', 'port', $this->port]);

        return $dataProvider;
    }
}
