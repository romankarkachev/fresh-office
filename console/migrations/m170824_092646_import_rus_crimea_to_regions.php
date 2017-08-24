<?php

use yii\db\Migration;

/**
 * Импортируется отсутствовавший ранее Крым в Россию.
 */
class m170824_092646_import_rus_crimea_to_regions extends Migration
{
    public function up()
    {
        $this->execute(file_get_contents(__DIR__ . '/_import_rus_crimea.sql'));
    }

    public function down()
    {
        \common\models\Cities::deleteAll(['region_id' => 88]);
        \common\models\Regions::deleteAll(['region_id' => 88]);
    }
}
