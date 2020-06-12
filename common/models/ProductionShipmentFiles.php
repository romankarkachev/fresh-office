<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;

/**
 * This is the model class for table "production_shipment_files".
 *
 * @property int $id
 * @property int $uploaded_at Дата и время загрузки
 * @property int $uploaded_by Автор загрузки
 * @property int $ps_id Отправка с производства
 * @property string $ffp Полный путь к файлу
 * @property string $fn Имя файла
 * @property string $ofn Оригинальное имя файла
 * @property int $size Размер файла
 *
 * @property ProductionShipment $ps
 * @property User $uploadedBy
 */
class ProductionShipmentFiles extends \yii\db\ActiveRecord
{
    /**
     * Идентификаторы элементов страницы
     */
    const DOM_IDS = [
        // контейнер для интерактивного обновления
        'PJAX_ID' => 'pjax-files',
        // таблица с файлами
        'GRIDVIEW_ID' => 'gwFiles',
        // инструмент загрузки файлов
        'FILE_INPUT_ID' => 'production-shipment-files',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'production_shipment_files';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ps_id', 'ffp', 'fn', 'ofn'], 'required'],
            [['uploaded_at', 'uploaded_by', 'ps_id', 'size'], 'integer'],
            [['ffp', 'fn', 'ofn'], 'string', 'max' => 255],
            [['ps_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductionShipment::class, 'targetAttribute' => ['ps_id' => 'id']],
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
            'ps_id' => 'Отправка с производства',
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
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            if (file_exists($this->ffp)) {
                if (FileHelper::unlink($this->ffp)) {
                    // файл успешно удален, проверим, не осталось ли файлов в его папке
                    $uploadsPath = self::getUploadsFilepath($this->ps);
                    if (count(FileHelper::findFiles($uploadsPath)) == 0) {
                        // после удаления файлов не осталось, удаляем папку для файлов компании
                        FileHelper::removeDirectory($uploadsPath);
                    }

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Возвращает путь к папке, в которую необходимо поместить загружаемые пользователем файлы.
     * Если папка не существует, она будет создана. Если создание провалится, будет возвращено false.
     * @param $model ProductionShipment
     * @return bool|string
     * @throws \yii\base\Exception
     */
    public static function getUploadsFilepath($model = null)
    {
        $filepath = Yii::getAlias('@uploads-ps-fs');
        if (!empty($model)) {
            $filepath .= '/' . $model->id;
        }
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
    public function getPs()
    {
        return $this->hasOne(ProductionShipment::class, ['id' => 'ps_id']);
    }
}
