<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "licenses_fkko_pages".
 *
 * @property integer $id
 * @property integer $fkko_id
 * @property integer $file_id
 *
 * @property LicensesFiles $file
 * @property Fkko $fkko
 */
class LicensesFkkoPages extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'licenses_fkko_pages';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fkko_id', 'file_id'], 'required'],
            [['fkko_id', 'file_id'], 'integer'],
            [['file_id'], 'exist', 'skipOnError' => true, 'targetClass' => LicensesFiles::className(), 'targetAttribute' => ['file_id' => 'id']],
            [['fkko_id'], 'exist', 'skipOnError' => true, 'targetClass' => Fkko::className(), 'targetAttribute' => ['fkko_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fkko_id' => 'Код ФККО',
            'file_id' => 'Файл со сканом страницы',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(LicensesFiles::className(), ['id' => 'file_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFkko()
    {
        return $this->hasOne(Fkko::className(), ['id' => 'fkko_id']);
    }
}
