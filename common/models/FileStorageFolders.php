<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "file_storage_folders".
 *
 * @property integer $id
 * @property integer $fo_ca_id
 * @property string $fo_ca_name
 * @property string $folder_name
 */
class FileStorageFolders extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'file_storage_folders';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fo_ca_id', 'fo_ca_name', 'folder_name'], 'required'],
            [['fo_ca_id'], 'integer'],
            [['fo_ca_name', 'folder_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'fo_ca_id' => 'ID контрагента из Fresh office',
            'fo_ca_name' => 'Наименование контрагента из Fresh office',
            'folder_name' => 'Имя папки',
        ];
    }
}
