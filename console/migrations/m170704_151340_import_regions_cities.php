<?php

use yii\db\Migration;

/**
 * Создаются три таблицы "Страны", "Регионы", "Города".
 */
class m170704_151340_import_regions_cities extends Migration
{
    public function up()
    {
        $this->execute(file_get_contents(__DIR__ . '/_import_country.sql'));
        $this->execute(file_get_contents(__DIR__ . '/_import_region.sql'));
        $this->execute(file_get_contents(__DIR__ . '/_import_city.sql'));
    }

    public function down()
    {
        $this->dropTable('city');
        $this->dropTable('region');
        $this->dropTable('country');
    }
}
