<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;
use dektrium\user\models\Profile;

/**
 * This is the model class for table "file_storage".
 *
 * @property integer $id
 * @property integer $uploaded_at
 * @property integer $uploaded_by
 * @property integer $ca_id
 * @property string $ca_name
 * @property integer $type_id
 * @property string $ffp
 * @property string $fn
 * @property string $ofn
 * @property integer $size
 *
 * @property string $uploadedByProfileName
 *
 * @property UploadingFilesMeanings $type
 * @property User $uploadedBy
 * @property Profile $uploadedByProfile
 */
class FileStorage extends \yii\db\ActiveRecord
{
    /**
     * путь к корневой папке хранилища
     */
    const ROOT_FOLDER = '/mnt/crmshare';

    /**
     * @var string наименование папки для поиска вручную, если система не смогла подобрать ее самостоятельно
     */
    public $folder_seek;

    /**
     * @var bool признак необходимости создания папки
     */
    public $needToCreateFolder;

    /**
     * @var string наименование папки контрагента
     */
    public $caFolderName;

    /**
     * @var UploadedFile
     */
    public $file;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'file_storage';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ffp', 'fn', 'ofn', 'file'], 'required'],
            [['ca_id', 'type_id', 'file'], 'required', 'on' => 'create'],
            [['ca_id', 'type_id'], 'required', 'on' => 'update'],
            [['uploaded_at', 'uploaded_by', 'ca_id', 'type_id', 'size', 'needToCreateFolder'], 'integer'],
            [['ca_name', 'ffp', 'fn', 'ofn'], 'string', 'max' => 255],
            ['caFolderName', 'safe'],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => UploadingFilesMeanings::className(), 'targetAttribute' => ['type_id' => 'id']],
            [['uploaded_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['uploaded_by' => 'id']],
            [['file'], 'file', 'skipOnEmpty' => true],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uploaded_at' => 'Дата и время загрузки',
            'uploaded_by' => 'Автор загрузки',
            'ca_id' => 'Контрагент',
            'ca_name' => 'Контрагент',
            'type_id' => 'Тип контента',
            'ffp' => 'Полный путь к файлу',
            'fn' => 'Имя файла',
            'ofn' => 'Оригинальное имя файла',
            'size' => 'Размер файла',
            'file' => 'Файл',
            // вычисляемые поля
            'uploadedByProfileName' => 'Загрузил',
            'typeName' => 'Тип',
            'caFolderName' => 'Папка контрагента',
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
                    ActiveRecord::EVENT_BEFORE_INSERT => ['uploaded_at'],
                ],
            ],
            'blameable' => [
                'class' => 'yii\behaviors\BlameableBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['uploaded_by'],
                ],
            ],
        ];
    }

    /**
     * Перед удалением информации о прикрепленном к сделке файле, удалим его физически с диска.
     * @return bool
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            if (file_exists($this->ffp)) unlink($this->ffp);

            return true;
        }
        else return false;
    }

    /**
     * Возвращает путь к папке, в которую необходимо поместить загружаемые пользователем файлы.
     * Если папка не существует, она будет создана. Если создание провалится, будет возвращено false.
     * @return bool|string
     */
    public function getUploadsFilepath()
    {
        if ($this->caFolderName == '' || $this->caFolderName == null) return false;

        $filepath = FileStorage::ROOT_FOLDER . '/' . $this->caFolderName;
        if (!is_dir($filepath)) {
            if (!FileHelper::createDirectory($filepath, 0775, true)) return false;
        }

        return realpath($filepath);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(UploadingFilesMeanings::className(), ['id' => 'type_id']);
    }

    /**
     * Возвращает наименование типа контента.
     * @return string
     */
    public function getTypeName()
    {
        return $this->type != null ? $this->type->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUploadedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'uploaded_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUploadedByProfile()
    {
        return $this->hasOne(Profile::className(), ['user_id' => 'uploaded_by']);
    }

    /**
     * Возвращает имя по профилю или имя пользователя, если в профиле пусто.
     * @return string
     */
    public function getUploadedByProfileName()
    {
        return $this->uploadedByProfile != null ? ($this->uploadedByProfile->name != null ? $this->uploadedByProfile->name : $this->uploadedByProfile->user->username) : '';
    }
}
