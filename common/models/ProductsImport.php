<?php

namespace common\models;

use yii\base\Exception;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

class ProductsImport extends Model
{
    const PRODUCT_TYPE_WASTE = 0; // отходы
    const PRODUCT_TYPE_PRODUCT = 1; // товары (услуги)

    /**
     * Тип номенклатуры.
     * @var integer
     */
    public $type;

    /**
     * @var UploadedFile
     */
    public $importFile;

    public function rules()
    {
        return [
            [['type'], 'integer'],
            [['importFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'xls,xlsx'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'type' => 'Тип номенклатуры',
            'importFile' => 'Файл',
        ];
    }

    /**
     * Возвращает массив допустимых типов номенклатуры.
     * @return array
     */
    public static function fetchTypes()
    {
        $result[] = [
            'id' => 0,
            'name' => 'Отходы',
        ];

        $result[] = [
            'id' => 1,
            'name' => 'Товары (услуги)',
        ];

        return $result;
    }

    /**
     * Возвращает массив типов номенклатуры. Применяется для вывода в виджетах Select2.
     * @return mixed
     */
    public static function arrayMapTypesForSelect2()
    {
        return ArrayHelper::map(self::fetchTypes(), 'id', 'name');
    }

    /**
     * Получает на вход дату в формате dd.mm.yyyy, отдает в формате yyyy-mm-dd.
     * @param string $src_date
     * @return string
     */
    public static function transformDate($src_date)
    {
        $result = '';

        if ($src_date != null && $src_date != '')
            try {
                $p = '~^(0?[1-9]|[12]\d|3[01])[-./ ](0?[1-9]|1[012])[-./ ]((19|20)?\d{2})$~';
                if ( preg_match ( $p, $src_date, $m ) )
                    $result = mktime ( 0, 0, 0, $m[2], $m[1], isset ( $m[4] ) ? $m[3] : 2000 + $m[3] );
            }
            catch (Exception $exception) {

            }
        return date('Y-m-d', $result);
    }

    /**
     * Делает первую букву в слове заглавной. Работает с мультибайтовыми кодировками.
     * Uppercase first letter. Working with multi-byte encodings.
     * @param string $str
     * @param string $encoding
     * @return string
     */
    public static function ucFirstRu($str, $encoding = 'UTF-8')
    {
        return mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding)
            . mb_substr($str, 1, null, $encoding);
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
        $name = str_replace('Оказание услуг по обращению с отходом: "', '', $name);
        //$name = mb_strtolower($name);
        //$name = ProductsImport::ucFirstRu($name);
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
