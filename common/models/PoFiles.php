<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;

/**
 * This is the model class for table "po_files".
 *
 * @property int $id
 * @property int $uploaded_at Дата и время загрузки
 * @property int $uploaded_by Автор загрузки
 * @property int $po_id Платежный ордер
 * @property string $ffp Полный путь к файлу
 * @property string $fn Имя файла
 * @property string $ofn Оригинальное имя файла
 * @property int $size Размер файла
 *
 * @property string $uploadedByProfileName
 *
 * @property Po $po
 * @property User $uploadedBy
 * @property Profile $uploadedByProfile
 */
class PoFiles extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'po_files';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['po_id', 'ffp', 'fn', 'ofn'], 'required'],
            [['uploaded_at', 'uploaded_by', 'po_id', 'size'], 'integer'],
            [['ffp', 'fn', 'ofn'], 'string', 'max' => 255],
            [['po_id'], 'exist', 'skipOnError' => true, 'targetClass' => Po::className(), 'targetAttribute' => ['po_id' => 'id']],
            [['uploaded_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['uploaded_by' => 'id']],
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
            'po_id' => 'Платежный ордер',
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
            if (file_exists($this->ffp)) unlink($this->ffp);

            return true;
        }
        else return false;
    }

    /**
     * Возвращает путь к папке, в которую необходимо поместить загружаемые пользователем файлы.
     * Если папка не существует, она будет создана. Если создание провалится, будет возвращено false.
     * @param $obj_id integer идентификатор родительской сущности, к которой прикрепляются файлы
     * @return mixed
     * @throws \yii\base\Exception
     */
    public static function getUploadsFilepath($obj_id = null)
    {
        $filepath = Yii::getAlias('@uploads-po-fs');
        if (!empty($obj_id)) {
            // если передается идентификатор родительской сущности, дополним тогда путь вложенными папками,
            // представляющими дату создания объекта и его идентификатор
            $object = Po::findOne($obj_id);
            if ($object) {
                // например: \uploads\po\1970\01\01\1524
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
    public function getPo()
    {
        return $this->hasOne(Po::className(), ['id' => 'po_id']);
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
