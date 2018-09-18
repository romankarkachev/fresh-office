<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "ce_attached_files".
 *
 * @property integer $id
 * @property integer $message_id
 * @property string $ofn
 * @property integer $size
 *
 * @property CEMessages $message
 */
class CEAttachedFiles extends \yii\db\ActiveRecord
{
    public $lettersIds;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ce_attached_files';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message_id', 'ofn'], 'required'],
            [['message_id', 'size'], 'integer'],
            [['ofn'], 'string', 'max' => 255],
            [['message_id'], 'exist', 'skipOnError' => true, 'targetClass' => CEMessages::className(), 'targetAttribute' => ['message_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'message_id' => 'Письмо',
            'ofn' => 'Оригинальное имя файла',
            'size' => 'Размер файла',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessage()
    {
        return $this->hasOne(CEMessages::className(), ['id' => 'message_id']);
    }
}
