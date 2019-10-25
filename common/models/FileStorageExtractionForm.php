<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\helpers\FileHelper;

/**
 * @property array $fo_ca_ids
 * @property array $type_ids
 */
class FileStorageExtractionForm extends Model
{
    /**
     * @var string идентификаторы контрагентов
     */
    public $fo_ca_ids;

    /**
     * @var string идентификаторы типов документов
     */
    public $type_ids;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fo_ca_ids', 'type_ids'], 'required'],
            [['fo_ca_ids', 'type_ids'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'fo_ca_ids' => 'Контрагенты',
            'type_ids' => 'Типы контента',
        ];
    }

    /**
     * Возвращает путь к папке, в которую необходимо поместить загружаемые пользователем файлы.
     * Если папка не существует, она будет создана. Если создание провалится, будет возвращено false.
     * @return bool|string
     */
    public static function getUploadsFilepath()
    {
        $filepath = Yii::getAlias('@uploads-temp-storage-fs');
        if (!is_dir($filepath)) {
            if (!FileHelper::createDirectory($filepath, 0775, true)) return false;
        }

        return realpath($filepath);
    }
}
