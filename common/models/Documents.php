<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use dektrium\user\models\Profile;

/**
 * This is the model class for table "documents".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $author_id
 * @property string $doc_date
 * @property string $fo_project
 * @property string $fo_customer
 * @property string $fo_contract
 * @property string $comment
 *
 * @property User $author
 * @property DocumentsHk[] $documentsHks
 * @property DocumentsTp[] $documentsTps
 */
class Documents extends \yii\db\ActiveRecord
{
    /**
     * Табличая часть документа.
     * @var array
     */
    public $tp;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'documents';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['author_id'], 'required'],
            [['created_at', 'author_id', 'fo_project', 'fo_customer', 'fo_contract'], 'integer'],
            [['doc_date'], 'safe'],
            [['comment'], 'string'],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Дата и время создания',
            'author_id' => 'Автор создания записи',
            'doc_date' => 'Дата документа',
            'fo_project' => 'ID проекта во Fresh Office',
            'fo_customer' => 'ID заказчика во Fresh Office',
            'fo_contract' => 'ID договора во Fresh Office',
            'comment' => 'Примечание',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthorProfile()
    {
        return $this->hasOne(Profile::className(), ['user_id' => 'author_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentsHks()
    {
        return $this->hasMany(DocumentsHk::className(), ['doc_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentsTps()
    {
        return $this->hasMany(DocumentsTp::className(), ['doc_id' => 'id']);
    }
}
