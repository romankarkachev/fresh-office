<?php

use yii\db\Migration;

class m170427_081616_adding_search_field_to_appeal_sources extends Migration
{
    public function up()
    {
        $this->addColumn('appeal_sources', 'search_field', $this->string(50)->comment('Подстрока для идентификации'));

        $appeal_sources = \common\models\AppealSources::find()->all();
        foreach ($appeal_sources AS $appeal_source)
            /* @var $appeal_source \common\models\AppealSources */

            // если встречаются только латинские символы, наименование источника копируем в новое поле
            if (preg_match('/[a-zA-Z]/', $appeal_source->name))
                $this->update('appeal_sources', [
                    'search_field' => $appeal_source->name,
                ], [
                    'id' => $appeal_source->id,
                ]);
    }

    public function down()
    {
        $this->dropColumn('appeal_sources', 'search_field');
    }
}
