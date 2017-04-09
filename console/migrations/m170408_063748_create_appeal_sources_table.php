<?php

use yii\db\Migration;

/**
 * Создается таблица "Источники обращения".
 */
class m170408_063748_create_appeal_sources_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Источники обращения"';
        }

        $this->createTable('appeal_sources', [
            'id' => $this->primaryKey(),
            'name' => $this->string(50)->notNull()->comment('Наименование'),
        ], $tableOptions);

        $this->insert('appeal_sources', [
            'name' => 'cuopp.ru',
        ]);

        $this->insert('appeal_sources', [
            'name' => 'st77.ru',
        ]);

        $this->insert('appeal_sources', [
            'name' => 'mus1.ru',
        ]);

        $this->insert('appeal_sources', [
            'name' => 'alkoutil.ru',
        ]);

        $this->insert('appeal_sources', [
            'name' => 'utilpharma.ru',
        ]);

        $this->insert('appeal_sources', [
            'name' => 'promothodi.ru',
        ]);

        $this->insert('appeal_sources', [
            'name' => 'promothod.ru',
        ]);

        $this->insert('appeal_sources', [
            'name' => 'tranzit-eco.ru',
        ]);

        $this->insert('appeal_sources', [
            'name' => 'ecocorporation.ru',
        ]);

        $this->insert('appeal_sources', [
            'name' => 'musor1.ru',
        ]);

        $this->insert('appeal_sources', [
            'name' => 'wlrf.ru',
        ]);

        $this->insert('appeal_sources', [
            'name' => 'nw-company.ru',
        ]);

        $this->insert('appeal_sources', [
            'name' => 'wastelogistic.ru',
        ]);

        $this->insert('appeal_sources', [
            'name' => 'vseothodi.ru',
        ]);

        $this->insert('appeal_sources', [
            'name' => 'ros-ecology.ru',
        ]);

        for ($i = 1; $i < 16; $i++)
            $this->insert('appeal_sources', [
                'name' => 'ш-' . $i,
            ]);

        $this->insert('appeal_sources', [
            'name' => 'Входящий звонок',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('appeal_sources');
    }
}
