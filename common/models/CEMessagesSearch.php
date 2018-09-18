<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\CEMessages;
use yii\helpers\ArrayHelper;

/**
 * CEMessagesSearch represents the model behind the search form about `common\models\CEMessages`.
 */
class CEMessagesSearch extends CEMessages
{
    /**
     * @var string поле для универсального поиска
     */
    public $searchEntire;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'detected_at', 'obtained_at', 'mailbox_id', 'uid', 'attachment_count', 'created_at', 'is_complete'], 'integer'],
            [['subject', 'body_text', 'body_html', 'header', 'searchEntire'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'searchEntire' => 'Универсальный поиск',
        ]);
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
        $query = CEMessages::find();
        $query->select([
            '*',
            'id' => 'ce_messages.id',
            'mailbox_id' => 'ce_messages.mailbox_id',
            /*
            'addressesLinear' => 'ceAddresses.details',
            'attachedFilenamesLinear' => 'ceAttachedFiles.details',
            */
            'addressesLinear' => '(
                SELECT GROUP_CONCAT(CONCAT_WS(" ", `ce_addresses`.`email`, `ce_addresses`.`name`) SEPARATOR ", ") FROM `ce_addresses`
	            WHERE `ce_addresses`.`message_id` = `ce_messages`.`id`
	            GROUP BY ce_addresses.message_id
            )',
            'attachedFilenamesLinear' => '(
                SELECT GROUP_CONCAT(`ce_attached_files`.`ofn` SEPARATOR ", ") FROM `ce_attached_files`
	            WHERE `ce_attached_files`.`message_id` = `ce_messages`.`id`
	            GROUP BY `message_id`
            )',
        ]);

        /*
        // присоединяем колонку, в которой будут перечислены в строку все адреса, связанные с письмом
        $query->leftJoin('(
            SELECT
                ce_addresses.message_id,
                GROUP_CONCAT(CONCAT_WS(" ", `ce_addresses`.`email`, `ce_addresses`.`name`) SEPARATOR ", ") AS details
            FROM ce_addresses
            GROUP BY ce_addresses.message_id
        ) AS ceAddresses', '`ceAddresses`.`message_id` = `ce_messages`.`id`');

        // присоединяем колонку, в которой будут перечислены все имена файлов, вложенные в письмо
        $query->leftJoin('(
            SELECT
                ce_attached_files.message_id,
                GROUP_CONCAT(`ce_attached_files`.`ofn` SEPARATOR ", ") AS details
            FROM ce_attached_files
            GROUP BY ce_attached_files.message_id
        ) AS ceAttachedFiles', '`ceAttachedFiles`.`message_id` = `ce_messages`.`id`');
        */

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'route' => 'mail',
                'pageSize' => 20,
            ],
            'sort' => [
                'route' => 'mail',
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes' => [
                    'id',
                    'detected_at',
                    'obtained_at',
                    'mailbox_id',
                    'uid',
                    'subject',
                    'body_text',
                    'body_html',
                    'attachment_count',
                    'header',
                    'created_at',
                    'is_complete',
                ],
            ],
        ]);

        $this->load($params);
        $query->joinWith(['fromBlock']);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if (!Yii::$app->user->can('root')) {
            // если у пользователя нет полных прав, то поиск осуществляется только в рамках тех почтовых ящиков,
            // которые ему открыл администратор
            $availableMailboxes = CEUsersAccess::find()->select('mailbox_id')->where(['user_id' => Yii::$app->user->id])->asArray()->column();
            if (count($availableMailboxes) > 0) {
                if (!empty($this->mailbox_id))
                    if (!is_array($this->mailbox_id) && in_array($this->mailbox_id, $availableMailboxes)) {
                        $query->andFilterWhere([
                            'mailbox_id' => $this->mailbox_id,
                        ]);
                    }
                    else {
                        // если нет доступа ящику, по которому выполняется отбор, то отдаем пустую выборку
                        $query->where('0=1');
                        return $dataProvider;
                    }
                else {
                    $query->andFilterWhere([
                        'mailbox_id' => $availableMailboxes,
                    ]);
                }
            }
            else {
                // если нет доступа ни к одному ящику, то отдаем пустую выборку
                $query->where('0=1');
                return $dataProvider;
            }
        }
        else {
            $query->andFilterWhere([
                'mailbox_id' => $this->mailbox_id,
            ]);
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'ce_messages.id' => $this->id,
            'detected_at' => $this->detected_at,
            'obtained_at' => $this->obtained_at,
            'uid' => $this->uid,
            'attachment_count' => $this->attachment_count,
            'created_at' => $this->created_at,
            'is_complete' => $this->is_complete,
        ]);

        if (!empty($this->searchEntire)) {
            $query->andWhere([
                'or',
                ['like', 'subject', $this->searchEntire],
                ['like', 'body_text', $this->searchEntire],
                //['like', 'addressesLinear', $this->searchEntire],
                //['like', 'attachedFilenamesLinear', $this->searchEntire],
            ]);
        }
        else
            $query->andFilterWhere(['like', 'subject', $this->subject])
                ->andFilterWhere(['like', 'body_text', $this->body_text])
                ->andFilterWhere(['like', 'body_html', $this->body_html])
                ->andFilterWhere(['like', 'header', $this->header]);

        return $dataProvider;
    }
}
