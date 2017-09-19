<?php

use yii\db\Migration;

/**
 * Создается таблица "Банковские реквизиты перевозчика".
 */
class m170915_234245_create_ferrymen_bank_details_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Банковские реквизиты перевозчика"';
        };

        $this->createTable('ferrymen_bank_details', [
            'id' => $this->primaryKey(),
            'ferryman_id' => $this->integer()->notNull()->comment('Перевозчик'),
            'name_full' => $this->string(200)->comment('Полное наименование'),
            'inn' => $this->string(12)->comment('ИНН'),
            'kpp' => $this->string(9)->comment('КПП'),
            'ogrn' => $this->string(15)->comment('ОГРН(ИП)'),
            'bank_an' => $this->string(25)->comment('Номер р/с'),
            'bank_bik' => $this->string(10)->comment('БИК банка'),
            'bank_name' => $this->string()->comment('Наименование банка'),
            'bank_ca' => $this->string(25)->comment('Корр. счет'),
            'comment' => $this->text()->comment('Примечания'),
        ], $tableOptions);

        $this->createIndex('ferryman_id', 'ferrymen_bank_details', 'ferryman_id');

        $this->addForeignKey('fk_ferrymen_bank_details_ferryman_id', 'ferrymen_bank_details', 'ferryman_id', 'ferrymen', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey('fk_ferrymen_bank_details_ferryman_id', 'ferrymen_bank_details');

        $this->dropIndex('ferryman_id', 'ferrymen_bank_details');

        $this->dropTable('ferrymen_bank_details');
    }
}
