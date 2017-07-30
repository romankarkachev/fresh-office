<?php

use yii\db\Migration;

/**
 * Создается таблица "Виды первичных документов".
 */
class m170730_101502_create_pad_kinds_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Виды первичных документов (primary accounting documents)"';
        };

        $this->createTable('pad_kinds', [
            'id' => $this->primaryKey(),
            'name' => $this->string(30)->notNull()->comment('Наименование'),
            'name_full' => $this->string(150)->notNull()->comment('Полное наименование'),
        ], $tableOptions);

        $this->insert('pad_kinds', [
            'name' => 'Счет',
            'name_full' => 'Счет на оплату',
        ]);

        $this->insert('pad_kinds', [
            'name' => 'Счет-фактура',
            'name_full' => 'Счет-фактура',
        ]);

        $this->insert('pad_kinds', [
            'name' => 'АВР',
            'name_full' => 'Акт выполненных работ',
        ]);

        $this->insert('pad_kinds', [
            'name' => 'АУ',
            'name_full' => 'Акт об утилизации',
        ]);

        $this->insert('pad_kinds', [
            'name' => 'ТТН',
            'name_full' => 'Товарно-транспортная накладная',
        ]);

        $this->insert('pad_kinds', [
            'name' => 'АПП',
            'name_full' => 'АПП',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('pad_kinds');
    }
}
