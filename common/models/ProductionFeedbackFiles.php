<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;
use dektrium\user\models\Profile;

/**
 * This is the model class for table "production_feedback_files".
 *
 * @property integer $id
 * @property integer $uploaded_at
 * @property integer $uploaded_by
 * @property integer $action
 * @property integer $project_id
 * @property integer $ca_id
 * @property string $thumb_ffp
 * @property string $thumb_fn
 * @property string $ffp
 * @property string $fn
 * @property string $ofn
 * @property integer $size
 *
 * @property User $uploadedBy
 */
class ProductionFeedbackFiles extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'production_feedback_files';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['action', 'project_id', 'ca_id', 'thumb_ffp', 'thumb_fn', 'ffp', 'fn', 'ofn'], 'required'],
            [['uploaded_at', 'uploaded_by', 'action', 'project_id', 'ca_id', 'size'], 'integer'],
            [['thumb_ffp', 'thumb_fn', 'ffp', 'fn', 'ofn'], 'string', 'max' => 255],
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
            'action' => 'Признак соответствия груза документам', // 1 - не соответствует, 2 - соответствует
            'project_id' => 'Проект',
            'ca_id' => 'Контрагент',
            'thumb_ffp' => 'Полный путь к файлу-миниатюре',
            'thumb_fn' => 'Имя файла-миниатюры',
            'ffp' => 'Полный путь к файлу',
            'fn' => 'Имя файла',
            'ofn' => 'Оригинальное имя файла',
            'size' => 'Размер файла',
            // вычисляемые поля
            'uploadedByName' => 'Автор загрузки',
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
     * Перед удалением информации о прикрепленном к сделке файле, удалим его и его миниатюру физически с диска.
     * @return bool
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            if (file_exists($this->ffp)) unlink($this->ffp);
            if (file_exists($this->thumb_ffp)) unlink($this->thumb_ffp);

            return true;
        }
        else return false;
    }

    /**
     * Возвращает путь к папке, в которую необходимо поместить загружаемые пользователем файлы.
     * Если папка не существует, она будет создана. Если создание провалится, будет возвращено false.
     * @return bool|string
     */
    public static function getUploadsFilepath()
    {
        $filepath = Yii::getAlias('@uploads-production-files-fs');
        if (!is_dir($filepath)) {
            if (!FileHelper::createDirectory($filepath, 0775, true)) return false;
        }

        return realpath($filepath);
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
        return $this->hasOne(Profile::className(), ['user_id' => 'created_by']);
    }

    /**
     * Возвращает имя автора загрузки файла.
     * @return string
     */
    public function getUploadedByName()
    {
        return $this->uploaded_by == null ? '' : ($this->uploadedBy->profile == null ? $this->uploadedBy->username : $this->uploadedBy->profile->name);
    }
}
