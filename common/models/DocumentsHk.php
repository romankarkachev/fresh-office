<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "documents_hk".
 *
 * @property integer $id
 * @property integer $doc_id
 * @property integer $hk_id
 *
 * @property HandlingKinds $hk
 * @property Documents $doc
 */
class DocumentsHk extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'documents_hk';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['doc_id', 'hk_id'], 'required'],
            [['doc_id', 'hk_id'], 'integer'],
            [['hk_id'], 'exist', 'skipOnError' => true, 'targetClass' => HandlingKinds::className(), 'targetAttribute' => ['hk_id' => 'id']],
            [['doc_id'], 'exist', 'skipOnError' => true, 'targetClass' => Documents::className(), 'targetAttribute' => ['doc_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'doc_id' => 'Документ',
            'hk_id' => 'Вид обращения',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHk()
    {
        return $this->hasOne(HandlingKinds::className(), ['id' => 'hk_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDoc()
    {
        return $this->hasOne(Documents::className(), ['id' => 'doc_id']);
    }
}
