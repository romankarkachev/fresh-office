<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "licenses_requests_fkko".
 *
 * @property integer $id
 * @property integer $lr_id
 * @property integer $fkko_id
 * @property integer $file_id
 *
 * @property LicensesFiles $file
 * @property Fkko $fkko
 * @property LicensesRequests $license
 */
class LicensesRequestsFkko extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'licenses_requests_fkko';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['lr_id', 'fkko_id'], 'required'],
            [['lr_id', 'fkko_id', 'file_id'], 'integer'],
            [['file_id'], 'exist', 'skipOnError' => true, 'targetClass' => LicensesFiles::className(), 'targetAttribute' => ['file_id' => 'id']],
            [['fkko_id'], 'exist', 'skipOnError' => true, 'targetClass' => Fkko::className(), 'targetAttribute' => ['fkko_id' => 'id']],
            [['lr_id'], 'exist', 'skipOnError' => true, 'targetClass' => LicensesRequests::className(), 'targetAttribute' => ['lr_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'lr_id' => 'Запрос на лицензию',
            'fkko_id' => 'ФККО',
            'file_id' => 'Файл со сканом',
            // вычисляемые поля
            'fkkoRep' => 'ФККО',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLicense()
    {
        return $this->hasOne(LicensesRequests::className(), ['id' => 'lr_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFkko()
    {
        return $this->hasOne(Fkko::className(), ['id' => 'fkko_id']);
    }

    /**
     * Возвращает представление кода ФККО.
     * @return string
     */
    public function getFkkoRep()
    {
        return $this->fkko != null ? $this->fkko->fkko_code . ' - ' . $this->fkko->fkko_name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(LicensesFiles::className(), ['id' => 'file_id']);
    }

    /**
     * Возвращает полный путь к изображению со сканом страницы лицензии.
     * @return string
     */
    public function getFileFfp()
    {
        return $this->file != null ? $this->file->ffp : '';
    }
}
