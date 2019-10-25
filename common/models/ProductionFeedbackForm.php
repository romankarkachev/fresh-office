<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\helpers\FileHelper;
use yii\imagine\Image;

/**
 * @property integer $action
 * @property integer $project_id
 * @property integer $ca_id
 * @property integer $ca_name
 * @property string $message_body
 * @property array $tp
 * @property array $files
 */
class ProductionFeedbackForm extends Model
{
    /**
     * Признак соответствия документов факту.
     * @var integer 1 - не соответствует, 2 - соответствут
     */
    public $action;

    /**
     * Идентификатор проекта.
     * @var integer
     */
    public $project_id;

    /**
     * Идентификатор контрагента.
     * @var integer
     */
    public $ca_id;

    /**
     * Наименование контрагента.
     * @var string
     */
    public $ca_name;

    /**
     * Тема письма.
     * @var string
     */
    public $message_subject;

    /**
     * Текст письма.
     * @var string
     */
    public $message_body;

    /**
     * @var float дополнительные поля с весом и объемом в ТТН и фактически
     */
    public $weightTtn;
    public $volumeTtn;
    public $weightFact;
    public $volumeFact;

    /**
     * Табличная часть документа для ввода фактических данных.
     * @var array
     */
    public $tp;

    /**
     * Прикрепленные к письму файлы.
     * @var array
     */
    public $files;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['weightTtn', 'weightFact'], 'required'],
            [['action', 'project_id', 'ca_id'], 'integer'],
            [['ca_name', 'message_subject'], 'string'],
            [['weightTtn', 'volumeTtn', 'weightFact', 'volumeFact'], 'double'],
            [['message_body', 'tp'], 'safe'],
            [['files'], 'file', 'skipOnEmpty' => true, 'maxFiles' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'action' => 'Признак соответствия',
            'project_id' => 'Проект',
            'ca_id' => 'ID контрагента',
            'ca_name' => 'Наименование контрагента',
            'tp' => 'Товары и услуги',
            'message_subject' => 'Тема письма',
            'message_body' => 'Текст письма',
            'files' => 'Файлы',
            'weightTtn' => 'ТТН вес',
            'volumeTtn' => 'ТТН объем',
            'weightFact' => 'Факт вес',
            'volumeFact' => 'Факт объем',
        ];
    }

    /**
     * Выполняет загрузку файлов из post-параметров, а также сохранение их имен в базу данных.
     * Каждый файл прикрепляется к текущему обращению.
     * @return array|bool
     */
    public function upload()
    {
        if ($this->validate()) {
            $files = [];

            // путь к папке, куда будет загружен файл
            $filepath = ProductionFeedbackFiles::getUploadsFilepath();
            $folder = date('Y') . '/' . date('m') . '/' . date('d') . '/' . $this->project_id;
            $pifp = $filepath . '/' . $folder;
            if (!FileHelper::createDirectory($pifp, 0775, true)) return false;

            foreach ($this->files as $file) {
                // имя и полный путь к файлу полноразмерного изображения
                $fileAttached_fn = strtolower(Yii::$app->security->generateRandomString() . '.' . $file->extension);
                $fileAttached_ffp = $pifp . '/' . $fileAttached_fn;

                // имя и полный путь к миниатюре
                $thumbFn = 'thumb_' . $fileAttached_fn;
                $thumbFfp = $pifp . '/' . $thumbFn;

                if ($file->saveAs($fileAttached_ffp) &&
                    Image::thumbnail($fileAttached_ffp, 800, 600)->save($fileAttached_ffp, ['quality' => 90]) &&
                    Image::thumbnail($fileAttached_ffp, 160, 120)->save($thumbFfp, ['quality' => 80])
                ) {
                    // заполняем поля записи в базе о загруженном успешно файле
                    $fileAttachedmodel = new ProductionFeedbackFiles([
                        'action' => $this->action,
                        'project_id' => $this->project_id,
                        'ca_id' => $this->ca_id,
                        'thumb_ffp' => $thumbFfp,
                        'thumb_fn' => $folder . '/' . $thumbFn,
                        'ffp' => $fileAttached_ffp,
                        'fn' => $folder . '/' . $fileAttached_fn,
                        'ofn' => $file->name,
                        'size' => filesize($fileAttached_ffp),
                    ]);

                    if ($fileAttachedmodel->save()) $files[] = $fileAttachedmodel->ffp;
                }
            }

            return $files;
        }

        return false;
    }

}
