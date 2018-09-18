<?php
namespace common\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * Создает копию значения в поле для индексирования, предварительно удалив из него пробелы и заловеркейсив его.
 *
 * @author Roman Karkachev <post@romankarkachev.ru>
 * @since 2.0
 */
class IndexFieldBehavior extends Behavior
{
    public $in_attribute;
    public $out_attribute;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'getIndexField'
        ];
    }

    /**
     * @param $source
     * @return mixed|string
     */
    public static function processValue($source)
    {
        $clean_value = str_replace(chr(32), '', $source);
        $clean_value = preg_replace("/[^a-zA-ZА-Яа-я0-9\s]/", '', $clean_value);
        $clean_value = mb_strtolower($clean_value);
        return $clean_value;
    }

    /**
     * Формирует значение для индексируемого поля.
     * Обработки: удаление пробелов, перевод символов в нижний регистр.
     * @param $event
     */
    public function getIndexField($event)
    {
        $this->owner->{$this->out_attribute} = self::processValue($this->owner->{$this->in_attribute});
    }
}