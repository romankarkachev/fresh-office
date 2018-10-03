<?php

use yii\db\Migration;

/**
 * Добавляется поле user_id, которое связывает водителя и пользователя системы (для возможности авторизоваться в мобильном приложении).
 */
class m180927_203019_adding_user_id_to_drivers extends Migration
{
    /**
     * Наименование поля, которое добавляется
     */
    const FIELD_NAME = 'user_id';

    /**
     * @var string наименование таблицы, в которую добавляется поле
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля
     */
    private $fkName;

    public function init()
    {
        $this->tableName = \common\models\Drivers::tableName();
        $this->fkName = 'fk_' . $this->tableName . '_' . self::FIELD_NAME;

        parent::init();
    }

    public function up()
    {
        $this->addColumn($this->tableName, self::FIELD_NAME, $this->integer()->comment('Сопоставленный пользователь системы (для авторизации в мобильном приложении)') . ' AFTER `updated_by`');

        $this->createIndex(self::FIELD_NAME, $this->tableName, self::FIELD_NAME);

        $this->addForeignKey($this->fkName, $this->tableName, self::FIELD_NAME, 'user', 'id');
    }

    public function down()
    {
        $this->dropForeignKey($this->fkName, $this->tableName);

        $this->dropIndex(self::FIELD_NAME, $this->tableName);

        $this->dropColumn($this->tableName, self::FIELD_NAME);
    }
}
