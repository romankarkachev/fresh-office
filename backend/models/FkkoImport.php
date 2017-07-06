<?php

namespace backend\models;

use yii\base\Model;
use yii\web\UploadedFile;

class FkkoImport extends Model
{
    /**
     * @var UploadedFile
     */
    public $importFile;

    public function rules()
    {
        return [
            [['importFile'], 'file', 'skipOnEmpty' => false, 'extensions' => ['xls', 'xlsx'], 'checkExtensionByMimeType' => false],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'importFile' => 'Файл',
        ];
    }

    /**
     * Получает на вход дату в формате mm-dd-yy, преобразовывает и отдает в формате yyyy-mm-dd.
     * @param string $src_date
     * @return string
     */
    public static function transformDate($src_date)
    {
        $result = '';
        $array = explode('-', $src_date);
        if (count($array) == 3) {
            return strval((intval($array[2]) < 100 ? (2000 + $array[2]) : $array[2]) . '-' . $array[0] . '-' . $array[1]);
        }

        return date('Y-m-d', $result);
    }

    /**
     * Переводит цифру в параметрах в римскую (до семи).
     * @param integer $class
     * @return string
     */
    public static function DangerClassRep($class)
    {
        if (!is_numeric($class)) return $class;

        switch ($class) {
            case 1:
                return 'I';
            case 2:
                return 'II';
            case 3:
                return 'III';
            case 4:
                return 'IV';
            case 5:
                return 'V';
            case 6:
                return 'VI';
            case 7:
                return 'VII';
        }

        return '';
    }

    /**
     * Очищает от мусора наименование, переданное в параметрах.
     * @param string $dirty_name
     * @return string
     */
    public static function cleanName($dirty_name)
    {
        $name = trim($dirty_name);
        $name = str_replace(chr(194).chr(160), '', $name);
        $name = str_replace('   ', ' ', $name);
        $name = str_replace('  ', ' ', $name);
        //$name = mb_strtolower($name);
        //$name = self::ucFirstRu($name);
        return $name;
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
