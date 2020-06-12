<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;

/**
 * This is the model class for table "edf_files".
 *
 * @property integer $id
 * @property integer $uploaded_at
 * @property integer $uploaded_by
 * @property integer $ed_id
 * @property string $ffp
 * @property string $fn
 * @property string $ofn
 * @property integer $size
 *
 * @property string $uploadedByProfileName
 *
 * @property Edf $ed
 * @property User $uploadedBy
 * @property Profile $uploadedByProfile
 */
class EdfFiles extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'edf_files';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ed_id', 'ffp', 'fn', 'ofn'], 'required'],
            [['uploaded_at', 'uploaded_by', 'ed_id', 'size'], 'integer'],
            [['ffp', 'fn', 'ofn'], 'string', 'max' => 255],
            [['ed_id'], 'exist', 'skipOnError' => true, 'targetClass' => Edf::className(), 'targetAttribute' => ['ed_id' => 'id']],
            [['uploaded_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['uploaded_by' => 'id']],
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
            'ed_id' => 'Электронный документ',
            'ffp' => 'Полный путь к файлу',
            'fn' => 'Имя файла',
            'ofn' => 'Оригинальное имя файла',
            'size' => 'Размер файла',
            // вычисляемые поля
            'uploadedByProfileName' => 'Автор загрузки',
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
     * Перед удалением информации о прикрепленном к объекту файле, удалим его физически с диска.
     * @return bool
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            if (file_exists($this->ffp) && false === stripos($this->ffp, FileStorage::ROOT_FOLDER)) unlink($this->ffp);

            return true;
        }
        else return false;
    }

    /**
     * Возвращает путь к папке, в которую необходимо поместить загружаемые пользователем файлы.
     * Если папка не существует, она будет создана. Если создание провалится, будет возвращено false.
     * @param $obj_id integer идентификатор родительской сущности, к которой прикрепляются файлы
     * @return mixed
     */
    public static function getUploadsFilepath($obj_id = null)
    {
        $filepath = Yii::getAlias('@uploads-edf-fs');
        if (!empty($obj_id)) {
            // если передается идентификатор родительской сущности, дополним тогда путь вложенными папками,
            // представляющими дату создания объекта и его идентификатор
            $object = Edf::findOne($obj_id);
            if ($object) {
                // например: \uploads\edf\1970\01\01\1524
                $filepath .= '/' . date('Y', $object->created_at) . '/' . date('m', $object->created_at) . '/' . date('d', $object->created_at) . '/' . $obj_id;
            }
        }

        if (!is_dir($filepath)) {
            if (!FileHelper::createDirectory($filepath)) return false;
        }

        return realpath($filepath);
    }

    /**
     * Проверяет, является ли файл изображением.
     * @return bool
     */
    public function isImage()
    {
        $is = @getimagesize($this->ffp);
        if ( !$is )
            return false;
        elseif ( !in_array($is[2], array(1,2,3)) )
            return false;
        else return true;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEd()
    {
        return $this->hasOne(Edf::className(), ['id' => 'ed_id']);
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
     * Возвращает имя пользователя, который загрузил файл.
     * @return string
     */
    public function getUploadedByProfileName()
    {
        return $this->uploadedByProfile != null ? ($this->uploadedByProfile->name != null ? $this->uploadedByProfile->name : $this->uploadedBy->username) : '';
    }
}
