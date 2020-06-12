<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

/**
 * Генерация набора форм по шаблонам. Отдает результат в виде архива на скачивание.
 *
 * @property string $varietyName
 * @property string $kindName
 *
 * @property TenderFormsVarietiesKinds $vk
 */
class TenderFormsTemplateForm extends Model
{
    /**
     * @var int идентификатор связки разновидности и формы
     */
    public $vk_id;

    /**
     * @var UploadedFile файл шаблона, который необходимо поместить в папку с шаблонами
     */
    public $file;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vk_id'], 'integer'],
            [['file'], 'file', 'skipOnEmpty' => true, 'maxFiles' => 0],
            [['vk_id'], 'exist', 'skipOnError' => true, 'targetClass' => TenderFormsVarietiesKinds::class, 'targetAttribute' => ['vk_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'vk_id' => 'Форма',
            'file' => 'Файл',
        ];
    }

    /**
     * @return TenderFormsVarietiesKinds
     */
    public function getVk()
    {
        return TenderFormsVarietiesKinds::findOne(['id' => $this->vk_id]);
    }

    /**
     * Возвращает наименование разновидности.
     * @return string
     */
    public function getVarietyName()
    {
        $vk = $this->vk;
        if (!empty($vk)) {
            $varietyName = $vk->varietyName;
            if (!empty($varietyName)) {
                return $varietyName;
            }
        }

        return '';
    }

    /**
     * Возвращает наименование формы.
     * @return string
     */
    public function getKindName()
    {
        $vk = $this->vk;
        if (!empty($vk)) {
            $kindName = $vk->kindName;
            if (!empty($kindName)) {
                return $kindName;
            }
        }

        return '';
    }
}
