<?php

use yii\db\Migration;
use \common\models\DocumentsTypes;
use \common\models\ContractTypes;

/**
 * Создается таблица "Шаблоны для электронных документов".
 */
class m181023_193047_create_edf_tmpls_table extends Migration
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
     * @var string наименование внешнего ключа для добавляемого поля type_id
     */
    private $fkTypeIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля ct_id
     */
    private $fkCtIdName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'edf_tmpls';
        $this->fkTypeIdName = 'fk_' . $this->tableName . '_' . self::FIELD_TYPE_ID_NAME;
        $this->fkCtIdName = 'fk_' . $this->tableName . '_' . self::FIELD_CT_ID_NAME;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Шаблоны для электронных документов"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            self::FIELD_TYPE_ID_NAME => $this->integer()->notNull()->comment('Тип документа'),
            self::FIELD_CT_ID_NAME => $this->integer()->comment('Тип договора'),
            self::FIELD_NAME_NAME => $this->string()->notNull()->comment('Название документа'),
            self::FIELD_FFP_NAME => $this->string()->notNull()->comment('Полный путь к шаблону'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_TYPE_ID_NAME, $this->tableName, self::FIELD_TYPE_ID_NAME);
        $this->createIndex(self::FIELD_CT_ID_NAME, $this->tableName, self::FIELD_CT_ID_NAME);

        $this->addForeignKey($this->fkTypeIdName, $this->tableName, self::FIELD_TYPE_ID_NAME, 'documents_types', 'id');
        $this->addForeignKey($this->fkCtIdName, $this->tableName, self::FIELD_CT_ID_NAME, 'contract_types', 'id');

        $this->batchInsert($this->tableName, [
            self::FIELD_TYPE_ID_NAME,
            self::FIELD_CT_ID_NAME,
            self::FIELD_NAME_NAME,
            self::FIELD_FFP_NAME,
        ], [
            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_ПРЕДОПЛАТА,
                'Договор',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_ПРЕДОПЛАТА . '/tmpl_contract.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_ПРЕДОПЛАТА,
                'Приложение 1',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_ПРЕДОПЛАТА . '/tmpl_add1.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_ПРЕДОПЛАТА,
                'Приложение 2 с 01.07',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_ПРЕДОПЛАТА . '/tmpl_add2.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_ПРЕДОПЛАТА,
                'Приложение 3 от 07.09.16',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_ПРЕДОПЛАТА . '/tmpl_add3.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_ПРЕДОПЛАТА,
                'Приложение 4',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_ПРЕДОПЛАТА . '/tmpl_add4.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_ПРЕДОПЛАТА,
                'Приложение 5',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_ПРЕДОПЛАТА . '/tmpl_add5.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_ПРЕДОПЛАТА,
                'Приложение 6',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_ПРЕДОПЛАТА . '/tmpl_add6.docx',
            ],

            ////////////////////////////////////////////////////////////////////////////////////////////////////////////

            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_ПОСТОПЛАТА,
                'Договор',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_ПОСТОПЛАТА . '/tmpl_contract.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_ПОСТОПЛАТА,
                'Приложение 1',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_ПОСТОПЛАТА . '/tmpl_add1.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_ПОСТОПЛАТА,
                'Приложение 2 с 01.07 (1)',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_ПОСТОПЛАТА . '/tmpl_add2.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_ПОСТОПЛАТА,
                'Приложение 3 от 07.09.16 (9)',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_ПОСТОПЛАТА . '/tmpl_add3.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_ПОСТОПЛАТА,
                'Приложение 4',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_ПОСТОПЛАТА . '/tmpl_add4.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_ПОСТОПЛАТА,
                'Приложение 5',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_ПОСТОПЛАТА . '/tmpl_add5.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_ПОСТОПЛАТА,
                'Приложение 6',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_ПОСТОПЛАТА . '/tmpl_add6.docx',
            ],

            ////////////////////////////////////////////////////////////////////////////////////////////////////////////

            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_ОПЛАТА_ЧАСТЯМИ,
                'Договор',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_ОПЛАТА_ЧАСТЯМИ . '/tmpl_contract.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_ОПЛАТА_ЧАСТЯМИ,
                'Приложение 1',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_ОПЛАТА_ЧАСТЯМИ . '/tmpl_add1.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_ОПЛАТА_ЧАСТЯМИ,
                'Приложение 2 с 01.07',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_ОПЛАТА_ЧАСТЯМИ . '/tmpl_add2.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_ОПЛАТА_ЧАСТЯМИ,
                'Приложение 3 от 07.09.16 (9)',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_ОПЛАТА_ЧАСТЯМИ . '/tmpl_add3.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_ОПЛАТА_ЧАСТЯМИ,
                'Приложение 4',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_ОПЛАТА_ЧАСТЯМИ . '/tmpl_add4.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_ОПЛАТА_ЧАСТЯМИ,
                'Приложение 5 (Акт приема-передачи)',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_ОПЛАТА_ЧАСТЯМИ . '/tmpl_add5.docx',
            ],

            ////////////////////////////////////////////////////////////////////////////////////////////////////////////

            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_РАЗОВЫЙ,
                'Договор',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_РАЗОВЫЙ . '/tmpl_contract.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_РАЗОВЫЙ,
                'Договор для посредников',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_РАЗОВЫЙ . '/tmpl_contract_p.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_РАЗОВЫЙ,
                'Приложение 1',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_РАЗОВЫЙ . '/tmpl_add1.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОГОВОР,
                ContractTypes::CONTRACT_TYPE_РАЗОВЫЙ,
                'Приложение 2',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОГОВОР . '-' . ContractTypes::CONTRACT_TYPE_РАЗОВЫЙ . '/tmpl_add2.docx',
            ],

            [
                DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ,
                null,
                'ДС в связи со сменой ген.директора',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ . '/tmpl_genDir.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ,
                null,
                'ДС на новое приложение к Договору',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ . '/tmpl_newAdd.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ,
                null,
                'ДС на пролонгацию',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ . '/tmpl_prolong.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ,
                null,
                'ДС о смене названия',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ . '/tmpl_nameChange.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ,
                null,
                'ДС о смене реквизитов',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ . '/tmpl_reqsChange.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ,
                null,
                'ДС об изменении цены договора',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ . '/tmpl_price.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ,
                null,
                'ДС переход на постоплату',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ . '/tmpl_factChange.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ,
                null,
                'Приложение  - ПОСТОПЛАТА',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ . '/tmpl_fact.docx',
            ],
            [
                DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ,
                null,
                'Соглашение о расторжении',
                '/var/www/html/uploads/export-templates/edf/' . DocumentsTypes::TYPE_ДОП_СОГЛАШЕНИЕ . '/tmpl_terminate.docx',
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey($this->fkCtIdName, $this->tableName);
        $this->dropForeignKey($this->fkTypeIdName, $this->tableName);

        $this->dropIndex(self::FIELD_CT_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_TYPE_ID_NAME, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
