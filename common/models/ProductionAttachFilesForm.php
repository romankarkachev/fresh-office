<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\helpers\FileHelper;

/**
 * @property integer $project_id
 * @property integer $ca_id
 * @property array $files
 *
 * @property foProjects $project
 */
class ProductionAttachFilesForm extends Model
{
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
            [['project_id', 'ca_id'], 'integer'],
            [['files'], 'file', 'skipOnEmpty' => true, 'maxFiles' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'project_id' => 'Проект',
            'ca_id' => 'ID контрагента',
            'files' => 'Файлы',
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

                if ($file->saveAs($fileAttached_ffp)) {
                    // заполняем поля записи в базе о загруженном успешно файле
                    $fileAttachedmodel = new ProductionFeedbackFiles([
                        'action' => 0,
                        'project_id' => $this->project_id,
                        'ca_id' => $this->project->ID_COMPANY,
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

    /**
     * @return foProjects|null
     */
    public function getProject()
    {
        return foProjects::findOne($this->project_id);
    }
}
