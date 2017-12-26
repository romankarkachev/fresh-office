<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tmp_enum_files".
 *
 * @property integer $id
 * @property string $folder_name
 * @property integer $type
 */
class FileStorageFilesEnumerator extends \yii\db\ActiveRecord
{
    /**
     * Типы записей.
     * Бывают обработанные папки, которые в парсере больше не выводятся никогда и отложенные, которые можно запросить
     * через отдельный механизм для детальной обработки.
     */
    const TYPE_ОБРАБОТАНА = 1;
    const TYPE_ОТЛОЖЕНА = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tmp_enum_files';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['folder_name'], 'required'],
            [['type'], 'integer'],
            [['folder_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'folder_name' => 'Имя папки',
            'type' => 'Тип (1 - папка обработана, 2 - постоянный игнор)',
        ];
    }
}
