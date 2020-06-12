<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Форма для массовой замены статей расходов в платежных ордерах по бюджету.
 * @author Roman Karkachev <post@romankarkachev.ru>
 */
class PoEiReplaceForm extends Model
{
    /**
     * @var integer искомая статья расходов
     */
    public $src_ei_id;

    /**
     * @var string наименование искомой статьи расходов
     */
    public $src_ei_name;

    /**
     * @var integer статья расходов, на которую нужно заменить
     */
    public $dest_ei_id;

    /**
     * @var bool при желании можно удалить исходную статью расходов
     */
    public $drop_released;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['src_ei_id', 'dest_ei_id'], 'required'],
            [['src_ei_id', 'dest_ei_id'], 'integer'],
            ['drop_released', 'boolean'],
            [['src_ei_id'], 'exist', 'skipOnError' => true, 'targetClass' => PoEi::class, 'targetAttribute' => ['src_ei_id' => 'id']],
            [['dest_ei_id'], 'exist', 'skipOnError' => true, 'targetClass' => PoEi::class, 'targetAttribute' => ['dest_ei_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'src_ei_id' => 'Искомая статья',
            'dest_ei_id' => 'Новая статья',
            'drop_released' => 'Удалить искомую',
        ];
    }
}
