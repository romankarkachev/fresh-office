<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "uploading_files_meanings".
 *
 * @property integer $id
 * @property string $name
 * @property string $keywords
 *
 * @property FileStorage[] $files
 */
class UploadingFilesMeanings extends \yii\db\ActiveRecord
{
    /**
     * Разновидности документов водителей
     */
    const ТИП_КОНТЕНТА_ВУ_ЛИЦЕВАЯ = 10;
    const ТИП_КОНТЕНТА_ВУ_ОБОРОТ = 11;
    const ТИП_КОНТЕНТА_ПАСПОРТ_ГЛАВНАЯ = 12;
    const ТИП_КОНТЕНТА_ПАСПОРТ_ПРОПИСКА = 13;

    /**
     * Разновидности документов транспортных средств
     */
    const ТИП_КОНТЕНТА_ДОГОВОР = 1;
    const ТИП_КОНТЕНТА_ТТН = 2;
    const ТИП_КОНТЕНТА_ДОПСОГЛАШЕНИЕ = 4;
    const ТИП_КОНТЕНТА_ОСАГО = 14;
    const ТИП_КОНТЕНТА_ПТС_ЛИЦЕВАЯ = 15;
    const ТИП_КОНТЕНТА_ПТС_ОБОРОТ = 16;
    const ТИП_КОНТЕНТА_СТС_ЛИЦЕВАЯ = 17;
    const ТИП_КОНТЕНТА_СТС_ОБОРОТ = 18;
    const ТИП_КОНТЕНТА_ДИАГНОСТИЧЕСКАЯ_КАРТА = 19;
    const ТИП_КОНТЕНТА_ФОТО_АВТОМОБИЛЯ = 20;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'uploading_files_meanings';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['keywords'], 'string'],
            [['name'], 'string', 'max' => 50],
            [['keywords'], 'default', 'value' => null],
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
            'keywords' => 'Ключевые слова', // по которым можно понять, что это именно тот самый тип
        ];
    }

    /**
     * Возвращает массив параметров для ссылки на приаттаченные файлы.
     * @param $file array массив с данными приаттаченного файла (id, имя файла и т.д.)
     * @return array
     */
    public static function optionsForAttachedFilesLink($file)
    {
        $result = [
            'class' => 'link-ajax',
        ];

        if (isset($file)) {
            $result['id'] = 'previewFile' . $file['id'];
            $result['data-id'] = $file['id'];
            $result['title'] = $file['ofn'];
        }

        return $result;
    }

    /**
     * Выполняет проверку, используется ли запись в других элементах.
     * @return bool
     */
    public function checkIfUsed()
    {
        if ($this->getFiles()->count() > 0) return true;

        return false;
    }

    /**
     * Делает выборку типов подгружаемых файлов и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::find()->where(['not', ['keywords' => null]])->orderBy('name')->all(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(FileStorage::className(), ['type_id' => 'id']);
    }
}
