<?php

use yii\db\Migration;
use \common\models\Organizations;

/**
 * Добавляется поле org_id в несколько таблиц.
 */
class m191028_162658_adding_org_id_to_few extends Migration
{
    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_ORG_ID_NAME = 'org_id';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_ORG_ID_NAME = 'fk_%TABLE_NAME%_' . self::FIELD_ORG_ID_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableName = \common\models\EcoProjects::tableName();
        $this->addColumn($tableName, self::FIELD_ORG_ID_NAME, $this->integer()->comment('Организация-исполнитель') . ' AFTER `created_by`');
        $this->createIndex(self::FIELD_ORG_ID_NAME, $tableName, self::FIELD_ORG_ID_NAME);
        $this->addForeignKey(str_replace('%TABLE_NAME%', $tableName, self::FK_ORG_ID_NAME), $tableName, self::FIELD_ORG_ID_NAME, Organizations::tableName(), 'id');

        $tableName = \common\models\EcoMc::tableName();
        $this->addColumn($tableName, self::FIELD_ORG_ID_NAME, $this->integer()->comment('Организация-исполнитель') . ' AFTER `created_by`');
        $this->createIndex(self::FIELD_ORG_ID_NAME, $tableName, self::FIELD_ORG_ID_NAME);
        $this->addForeignKey(str_replace('%TABLE_NAME%', $tableName, self::FK_ORG_ID_NAME), $tableName, self::FIELD_ORG_ID_NAME, Organizations::tableName(), 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $tableName = \common\models\EcoMc::tableName();
        $this->dropForeignKey(str_replace('%TABLE_NAME%', $tableName, self::FK_ORG_ID_NAME), $tableName);
        $this->dropIndex(self::FIELD_ORG_ID_NAME, $tableName);
        $this->dropColumn($tableName, self::FIELD_ORG_ID_NAME);

        $tableName = \common\models\EcoProjects::tableName();
        $this->dropForeignKey(str_replace('%TABLE_NAME%', $tableName, self::FK_ORG_ID_NAME), $tableName);
        $this->dropIndex(self::FIELD_ORG_ID_NAME, $tableName);
        $this->dropColumn($tableName, self::FIELD_ORG_ID_NAME);
    }
}
