<?php

use yii\db\Migration;

/**
 * Добавляются поля "Количество", "Цена" и "Класс опасности" в табличную часть электронного документа.
 */
class m190313_103947_enhancing_edf_tp extends Migration
{
    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_DC_ID_NAME = 'dc_id';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля dc_id
     */
    private $fkDcIdName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'edf_tp';
        $this->fkDcIdName = 'fk_' . $this->tableName . '_' . self::FIELD_DC_ID_NAME;

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn($this->tableName, 'dc_id', $this->integer()->comment('Класс опасности') . ' AFTER `fkko_name`');
        $this->addColumn($this->tableName, 'amount', $this->decimal(12,2)->comment('Стоимость'));
        $this->alterColumn($this->tableName, 'measure', $this->decimal(12,3)->comment('Количество'));

        $this->createIndex(self::FIELD_DC_ID_NAME, $this->tableName, self::FIELD_DC_ID_NAME);

        $this->addForeignKey($this->fkDcIdName, $this->tableName, self::FIELD_DC_ID_NAME, 'danger_classes', 'id');

        $this->addCommentOnColumn($this->tableName, 'price', 'Цена');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addCommentOnColumn($this->tableName, 'price', 'Стоимость');

        $this->dropForeignKey($this->fkDcIdName, $this->tableName);

        $this->dropIndex(self::FIELD_DC_ID_NAME, $this->tableName);

        $this->dropColumn($this->tableName, 'amount');
        $this->dropColumn($this->tableName, 'dc_id');
    }
}
