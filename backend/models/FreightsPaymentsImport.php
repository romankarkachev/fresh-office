<?php

namespace backend\models;

use yii\base\Model;
use yii\web\UploadedFile;

class FreightsPaymentsImport extends Model
{
    /**
     * @var string дата, которая будет проставлена всем проектам из импортируемого файла
     */
    public $date_payment;

    /**
     * @var UploadedFile
     */
    public $importFile;

    public function rules()
    {
        return [
            ['date_payment', 'required'],
            [['importFile'], 'file', 'skipOnEmpty' => false, 'extensions' => ['xls', 'xlsx'], 'checkExtensionByMimeType' => false],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'date_payment' => 'Дата оплаты',
            'importFile' => 'Файл',
        ];
    }

    /**
     * @param string $filename
     * @return bool
     */
    public function upload($filename)
    {
        if ($this->validate()) {
            $upl_dir = \Yii::getAlias('@uploads');
            if (!file_exists($upl_dir) && !is_dir($upl_dir)) mkdir($upl_dir, 0755);

            $this->importFile->saveAs($filename);
            return true;
        } else {
            return false;
        }
    }
}
