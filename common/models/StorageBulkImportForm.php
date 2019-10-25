<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * @property string $type_id
 *
 * @property UploadingFilesMeanings $type
 */
class StorageBulkImportForm extends Model
{
    /**
     * @var integer тип контента
     */
    public $type_id;

    /**
     * @var array файлы для помещения в хранилище
     */
    public $files;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type_id'], 'required'],
            [['type_id'], 'integer'],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => UploadingFilesMeanings::class, 'targetAttribute' => ['type_id' => 'id']],
            [['files'], 'file', 'skipOnEmpty' => true, 'maxFiles' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'type_id' => 'Тип контента',
            'files' => 'Файлы',
        ];
    }
}
