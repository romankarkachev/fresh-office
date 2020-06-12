<?php

use yii\db\Migration;

/**
 * В таблицу "Получатели уведомлений о просрочке" добавляются поля, позволяющие вести учет также по пакетам корреспонденции.
 */
class m191130_104352_enhancing_notif_receivers_sncbt extends Migration
{
    /**
     * @var string наименование таблицы, которая обслуживается
     */
    const TABLE_NAME = 'notif_receivers_sncbt';

    /**
     * Поля
     */
    const FIELD_SECTION_NAME = 'section';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(self::TABLE_NAME, self::FIELD_SECTION_NAME, 'TINYINT(1) NOT NULL DEFAULT"1" COMMENT"Раздел учета (1 - проекты, 2 - пакеты корреспонденции)" AFTER `id`');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(self::TABLE_NAME, self::FIELD_SECTION_NAME);
    }
}
