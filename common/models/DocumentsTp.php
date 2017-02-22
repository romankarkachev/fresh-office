<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "documents_tp".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $author_id
 * @property integer $doc_id
 * @property integer $product_id
 * @property string $quantity
 * @property string $dc
 * @property integer $is_printable
 *
 * @property string $productName
 * @property Products $product
 * @property User $author
 * @property Documents $doc
 */
class DocumentsTp extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'documents_tp';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['author_id', 'doc_id', 'product_id'], 'required'],
            [['created_at', 'author_id', 'doc_id', 'product_id', 'is_printable'], 'integer'],
            [['quantity'], 'number'],
            [['dc'], 'string', 'max' => 10],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['author_id' => 'id']],
            [['doc_id'], 'exist', 'skipOnError' => true, 'targetClass' => Documents::className(), 'targetAttribute' => ['doc_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Products::className(), 'targetAttribute' => ['product_id' => 'id']],
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
            'doc_id' => 'Документ',
            'product_id' => 'Номенклатура',
            'quantity' => 'Количество',
            'dc' => 'Класс опасности',
            'is_printable' => 'Выводить на печать',
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
    public function getProduct()
    {
        return $this->hasOne(Products::className(), ['id' => 'product_id']);
    }

    /**
     * Возвращает наименование номенклатуры.
     * @return string
     */
    public function getProductName()
    {
        return $this->product != null ? $this->product->name : '';
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
    public function getDoc()
    {
        return $this->hasOne(Documents::className(), ['id' => 'doc_id']);
    }
}
