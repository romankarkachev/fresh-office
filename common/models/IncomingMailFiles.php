<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "incoming_mail_files".
 *
 * @property int $id
 * @property int $uploaded_at Дата и время загрузки
 * @property int $uploaded_by Автор загрузки
 * @property int $im_id Входящая корреспонденция
 * @property string $ffp Полный путь к файлу
 * @property string $fn Имя файла
 * @property string $ofn Оригинальное имя файла
 * @property int $size Размер файла
 *
 * @property User $uploadedBy
 * @property IncomingMail $im
 */
class IncomingMailFiles extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'incoming_mail_files';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['im_id', 'ffp', 'fn', 'ofn'], 'required'],
            [['uploaded_at', 'uploaded_by', 'im_id', 'size'], 'integer'],
            [['ffp', 'fn', 'ofn'], 'string', 'max' => 255],
            [['im_id'], 'exist', 'skipOnError' => true, 'targetClass' => IncomingMail::class, 'targetAttribute' => ['im_id' => 'id']],
            [['uploaded_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['uploaded_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uploaded_at' => 'Дата и время загрузки',
            'uploaded_by' => 'Автор загрузки',
            'im_id' => 'Входящая корреспонденция',
            'ffp' => 'Полный путь к файлу',
            'fn' => 'Имя файла',
            'ofn' => 'Оригинальное имя файла',
            'size' => 'Размер файла',
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
                'preserveNonEmptyValues' => true,
            ],
            'blameable' => [
                'class' => 'yii\behaviors\BlameableBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['uploaded_by'],
                ],
                'preserveNonEmptyValues' => true,
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
            if (file_exists($this->ffp)) unlink($this->ffp);

            return true;
        }
        else return false;
    }

    /**
     * Возвращает путь к папке, в которую необходимо поместить загружаемые пользователем файлы.
     * Если папка не существует, она будет создана. Если создание провалится, будет возвращено false.
     * @param $im IncomingMail
     * @return bool|string
     * @throws \yii\base\Exception
     */
    public static function getUploadsFilepath($im)
    {
        if (!empty($im)) {
            $filepath = Yii::getAlias('@uploads-incoming-mail-fs') . '/' . date('Y', $im->created_at) . '/' . date('m', $im->created_at) . '/' . date('d', $im->created_at) . '/' . $im->id;
            if (!is_dir($filepath)) {
                if (!\yii\helpers\FileHelper::createDirectory($filepath, 0775, true)) return false;
            }

            return realpath($filepath);
        }

        return false;
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
    public function getIm()
    {
        return $this->hasOne(IncomingMail::className(), ['id' => 'im_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUploadedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'uploaded_by']);
    }
}
