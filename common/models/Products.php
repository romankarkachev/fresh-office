<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "products".
 *
 * @property integer $id
 * @property string $name
 * @property integer $created_at
 * @property integer $is_deleted
 * @property integer $author_id
 * @property integer $type
 * @property string $unit
 * @property string $uw
 * @property string $dc
 * @property integer $fkko
 * @property string $fkko_date
 * @property integer $fo_id
 * @property string $fo_name
 * @property string $fo_fkko
 *
 * @property User $author
 */
class Products extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'products';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'author_id'], 'required'],
            [['name', 'fo_name'], 'string'],
            [['created_at', 'is_deleted', 'author_id', 'type', 'fkko', 'fo_id'], 'integer'],
            [['fkko_date'], 'safe'],
            [['unit'], 'string', 'max' => 30],
            [['uw'], 'string', 'max' => 50],
            [['dc'], 'string', 'max' => 10],
            [['fo_fkko'], 'string', 'max' => 20],
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
            'name' => 'Наименование',
            'created_at' => 'Дата и время создания',
            'is_deleted' => '0 - активен, 1 - пометка удаления',
            'author_id' => 'Автор создания записи',
            'type' => '0 - отходы, 1 - товары (услуги)',
            'unit' => 'Единица измерения',
            'uw' => 'Способ утилизации',
            'dc' => 'Класс опасности',
            'fkko' => 'Код по ФККО-2014',
            'fkko_date' => 'Дата внесения в ФККО',
            'fo_id' => 'Код из Fresh Office',
            'fo_name' => 'Наименование из Fresh Office',
            'fo_fkko' => 'Код ФККО из Fresh Office',
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
}
