<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "file_storage_stats".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $type
 * @property integer $fs_id
 * @property integer $ca_id
 * @property string $ca_name
 *
 * @property string $createdByProfileName
 *
 * @property FileStorage $fs
 * @property User $createdBy
 * @property Profile $createdByProfile
 */
class FileStorageStats extends \yii\db\ActiveRecord
{
    /**
     * @var integer допустимые значения в поле "Тип"
     */
    const STAT_TYPE_ПРОСМОТР = 1;
    const STAT_TYPE_СКАЧИВАНИЕ = 2;
    const STAT_TYPE_ЗАГРУЗКА_НА_СЕРВЕР = 3;

    /**
     * Время, за которое возможно нарастить только один просмотр файла. В течение пяти минут файл может быть просмотрен
     * любое количество раз, все равно просмотр уже засчитан.
     * @var integer количество в секундах
     */
    const FREE_TIME_TO_PREVIEW_FILE = 5 * 60;

    /**
     * Время, за которое возможно нарастить только одно скачивание файла. В течение этого времени файл может быть скачан
     * текущим пользователем любое количество раз, поскольку скачивание все равно уже засчитано.
     * @var integer количество в секундах
     */
    const FREE_TIME_TO_DOWNLOAD_FILE = 5 * 60;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'file_storage_stats';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fs_id'], 'required'],
            [['created_at', 'created_by', 'type', 'fs_id'], 'integer'],
            [['fs_id'], 'exist', 'skipOnError' => true, 'targetClass' => FileStorage::className(), 'targetAttribute' => ['fs_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Дата и время создания',
            'created_by' => 'Автор создания',
            'type' => 'Тип показателя', // 1 - просмотр, 2 - скачивание
            'fs_id' => 'Файл',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFs()
    {
        return $this->hasOne(FileStorage::className(), ['id' => 'fs_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedByProfile()
    {
        return $this->hasOne(Profile::className(), ['user_id' => 'created_by']);
    }

    /**
     * Возвращает имя создателя записи.
     * @return string
     */
    public function getCreatedByProfileName()
    {
        return $this->createdByProfile != null ? ($this->createdByProfile->name != null ? $this->createdByProfile->name : $this->createdBy->username) : '';
    }
}
