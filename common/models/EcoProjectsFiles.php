<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;

/**
 * This is the model class for table "eco_projects_files".
 *
 * @property int $id
 * @property int $uploaded_at Дата и время загрузки
 * @property int $uploaded_by Автор загрузки
 * @property int $project_id Проект
 * @property string $ffp Полный путь к файлу
 * @property string $fn Имя файла
 * @property string $ofn Оригинальное имя файла
 * @property int $size Размер файла
 *
 * @property EcoProjects $project
 * @property User $uploadedBy
 */
class EcoProjectsFiles extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'eco_projects_files';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project_id', 'ffp', 'fn', 'ofn'], 'required'],
            [['uploaded_at', 'uploaded_by', 'project_id', 'size'], 'integer'],
            [['ffp', 'fn', 'ofn'], 'string', 'max' => 255],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => EcoProjects::class, 'targetAttribute' => ['project_id' => 'id']],
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
            'project_id' => 'Проект',
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
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            if (file_exists($this->ffp)) {
                if (FileHelper::unlink($this->ffp)) {
                    // файл успешно удален, проверим, не осталось ли файлов в его папке
                    $uploadsPath = self::getUploadsFilepath($this->project_id);
                    if (count(FileHelper::findFiles($uploadsPath)) == 0) {
                        // после удаления файлов не осталось, удаляем папку для файлов компании
                        FileHelper::removeDirectory($uploadsPath);
                    }
                }
            }

            return true;
        }
        else return false;
    }

    /**
     * Возвращает путь к папке, в которую необходимо поместить загружаемые пользователем файлы.
     * Если папка не существует, она будет создана. Если создание провалится, будет возвращено false.
     * @param $id integer идентификатор проекта по экологии
     * @return mixed
     * @throws \yii\base\Exception
     */
    public static function getUploadsFilepath($id)
    {
        $filepath = Yii::getAlias('@uploads-eco-projects-fs') . '/' . $id;
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
    public function getProject()
    {
        return $this->hasOne(EcoProjects::class, ['id' => 'project_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUploadedBy()
    {
        return $this->hasOne(User::class, ['id' => 'uploaded_by']);
    }
}
