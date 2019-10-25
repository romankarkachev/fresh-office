<?php

namespace common\behaviors;

use yii\behaviors\AttributeBehavior;
use yii\db\ActiveRecord;
use common\components\GuidGenerator;

/**
 * Генерирует GUID.
 *
 * @author Roman Karkachev <post@romankarkachev.ru>
 * @since 2.0
 */
class GUIDFieldBehavior extends AttributeBehavior
{
    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'getGuidField'
        ];
    }

    /**
     * @return string
     */
    public static function processValue()
    {
        return GuidGenerator::GUIDv4();
    }

    /**
     * Формирует значение для поля с уникальным идентификатором.
     * @param $event
     */
    public function getGuidField($event)
    {
        $this->owner->guid = self::processValue();
    }
}
