<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tenders_files".
 *
 * @property int $id
 * @property int $uploaded_at Дата и время загрузки
 * @property int $uploaded_by Автор загрузки
 * @property int $tender_id Тендер
 * @property string $ffp Полный путь к файлу
 * @property string $fn Имя файла
 * @property string $ofn Оригинальное имя файла
 * @property int $size Размер файла
 * @property string $revision Редакция заявки
 * @property string $src_id Идентификатор в источнике
 * @property int $ct_id Тип контента
 *
 * @property User $uploadedBy
 * @property Tenders $tender
 * @property TendersContentTypes $ct
 */
class TendersFiles extends \yii\db\ActiveRecord
{
    const DOM_IDS = [
        // текст на кнопке в списке файлов
        'BUTTON_DOWNLOAD_SELECTED_PROMPT' => 'скачать выбранные файлы',
        // форма для интерактивного отбора по списку файлов
        'PJAX_SEARCH_FORM_ID' => 'frmSearchFiles',
        // таблица с файлами
        'GRIDVIEW_ID' => 'gwFiles',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tenders_files';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tender_id', 'ffp', 'fn', 'ofn'], 'required'],
            [['uploaded_at', 'uploaded_by', 'tender_id', 'size', 'ct_id'], 'integer'],
            [['ffp', 'fn', 'ofn'], 'string', 'max' => 255],
            [['revision'], 'string', 'max' => 3],
            [['src_id'], 'string', 'max' => 50],
            [['revision', 'src_id'], 'trim'],
            [['revision', 'src_id'], 'default', 'value' => null],
            [['uploaded_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['uploaded_by' => 'id']],
            [['tender_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tenders::class, 'targetAttribute' => ['tender_id' => 'id']],
            [['ct_id'], 'exist', 'skipOnError' => true, 'targetClass' => TendersContentTypes::class, 'targetAttribute' => ['ct_id' => 'id']],
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
            'tender_id' => 'Тендер',
            'ffp' => 'Полный путь к файлу',
            'fn' => 'Имя файла',
            'ofn' => 'Оригинальное имя файла',
            'size' => 'Размер файла',
            'revision' => 'Редакция заявки',
            'src_id' => 'Идентификатор в источнике',
            'ct_id' => 'Тип контента',
            // вычисляемые поля
            'ctName' => 'Тип контента',
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
     * @param $tender Tenders
     * @return bool|string
     * @throws \yii\base\Exception
     */
    public static function getUploadsFilepath($tender)
    {
        $placedAt = $tender->placed_at;
        if (empty($placedAt && !empty($tender->id))) {
            $placedAt = $tender->created_at;
        }
        $filepath = Yii::getAlias('@uploads-tenders-fs') . '/' . date('Y', $placedAt) . '/' . date('m', $placedAt) . '/' . date('d', $placedAt) . '/' . $tender->id;
        if (!is_dir($filepath)) {
            if (!\yii\helpers\FileHelper::createDirectory($filepath, 0775, true)) return false;
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
    public function getUploadedBy()
    {
        return $this->hasOne(User::class, ['id' => 'uploaded_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTender()
    {
        return $this->hasOne(Tenders::class, ['id' => 'tender_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCt()
    {
        return $this->hasOne(TendersContentTypes::class, ['id' => 'ct_id']);
    }

    /**
     * Возвращает наименвание типа контента приаттаченного файла.
     * @return string
     */
    public function getCtName()
    {
        return !empty($this->ct) ? $this->ct->name : '';
    }
}
