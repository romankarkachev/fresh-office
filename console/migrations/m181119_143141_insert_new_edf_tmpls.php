<?php

use yii\db\Migration;
use \common\models\DocumentsTypes;
use \common\models\ContractTypes;

/**
 * Вставляются новые типы договоров, а также для них шаблоны.
 */
class m181119_143141_insert_new_edf_tmpls extends Migration
{
    /**
     * Поля, которые использованы в разных местах
     * В целях рефакторинга имена собраны в одном месте
     */
    const FIELD_TYPE_ID_NAME = 'type_id';
    const FIELD_CT_ID_NAME = 'ct_id';
    const FIELD_NAME_NAME = 'name';
    const FIELD_FFP_NAME = 'ffp';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'edf_tmpls';

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->batchInsert('contract_types', ['id', 'name'], [
            [
                'id' => 5,
                'name' => 'Предоплата без транспорта',
            ],
            [
                'id' => 6,
                'name' => 'Постоплата без транспорта',
            ],
            [
                'id' => 7,
                'name' => 'Предоплата на одну сделку',
            ],
        ]);

        $this->batchInsert($this->tableName, [
            self::FIELD_TYPE_ID_NAME,
            self::FIELD_CT_ID_NAME,
            self::FIELD_NAME_NAME,
            self::FIELD_FFP_NAME,
        ], [
            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_ПРЕДОПЛАТА_БТ,
                'Договор предоплата без приложения по транспорту',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_ПРЕДОПЛАТА_БТ . '/tmpl_contract.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_ПОСТОПЛАТА_БТ,
                'Договор постоплата без приложения по транспорту',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_ПОСТОПЛАТА_БТ . '/tmpl_contract.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_НА_ОДНУ_СДЕЛКУ,
                'Договор предоплата на одну сделку',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_НА_ОДНУ_СДЕЛКУ . '/tmpl_contract.docx',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete($this->tableName, [
            self::FIELD_TYPE_ID_NAME => DocumentsTypes::TYPE_ДОГОВОР,
            self::FIELD_CT_ID_NAME => ContractTypes::CONTRACT_TYPE_ПРЕДОПЛАТА_БТ,
        ]);
        $this->delete($this->tableName, [
            self::FIELD_TYPE_ID_NAME => DocumentsTypes::TYPE_ДОГОВОР,
            self::FIELD_CT_ID_NAME => ContractTypes::CONTRACT_TYPE_ПОСТОПЛАТА_БТ,
        ]);
        $this->delete($this->tableName, [
            self::FIELD_TYPE_ID_NAME => DocumentsTypes::TYPE_ДОГОВОР,
            self::FIELD_CT_ID_NAME => ContractTypes::CONTRACT_TYPE_НА_ОДНУ_СДЕЛКУ,
        ]);

        $this->delete('contract_types', [
            'id' => [5,6,7],
        ]);
    }
}
