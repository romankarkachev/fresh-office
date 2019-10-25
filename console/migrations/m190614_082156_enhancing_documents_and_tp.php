<?php

use yii\db\Migration;

/**
 * Огромная работа над товарами и документами.
 */
class m190614_082156_enhancing_documents_and_tp extends Migration
{
    /**
     * @var string наименование таблиц, которые преображаются
     */
    const TABLE_PRODUCTS_NAME = 'products';
    const TABLE_DOCUMENTS_NAME = 'documents';
    const TABLE_TP_NAME = 'documents_tp';

    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_PRODUCTS_AUTHOR_ID_NAME = 'author_id';
    const FIELD_PRODUCTS_CREATED_BY_NAME = 'created_by';
    const FIELD_PRODUCTS_UNIT_ID_NAME = 'unit_id';
    const FIELD_PRODUCTS_HK_ID_NAME = 'hk_id';
    const FIELD_PRODUCTS_DC_ID_NAME = 'dc_id';
    const FIELD_PRODUCTS_FKKO_ID_NAME = 'fkko_id';

    const FIELD_ORG_ID_NAME = 'org_id';
    const FIELD_ED_ID_NAME = 'ed_id';

    const FIELD_TP_CREATED_AT_NAME = 'created_at';
    const FIELD_TP_AUTHOR_ID_NAME = 'author_id';
    const FIELD_TP_PRODUCT_ID_NAME = 'product_id';
    const FIELD_TP_UNIT_ID_NAME = 'unit_id';
    const FIELD_TP_HK_ID_NAME = 'hk_id';
    const FIELD_TP_DC_ID_NAME = 'dc_id';
    const FIELD_TP_FKKO_ID_NAME = 'fkko_id';

    /**
     * Наименования внешних ключей для добавляемых полей
     */
    const FK_PRODUCTS_AUTHOR_ID_NAME = 'fk_' . self::TABLE_PRODUCTS_NAME . '_' . self::FIELD_PRODUCTS_AUTHOR_ID_NAME;
    const FK_PRODUCTS_CREATED_BY_NAME = 'fk_' . self::TABLE_PRODUCTS_NAME . '_' . self::FIELD_PRODUCTS_CREATED_BY_NAME;
    const FK_PRODUCTS_UNIT_ID_NAME = 'fk_' . self::TABLE_PRODUCTS_NAME . '_' . self::FIELD_PRODUCTS_UNIT_ID_NAME;
    const FK_PRODUCTS_HK_ID_NAME = 'fk_' . self::TABLE_PRODUCTS_NAME . '_' . self::FIELD_PRODUCTS_HK_ID_NAME;
    const FK_PRODUCTS_DC_ID_NAME = 'fk_' . self::TABLE_PRODUCTS_NAME . '_' . self::FIELD_PRODUCTS_DC_ID_NAME;
    const FK_PRODUCTS_FKKO_ID_NAME = 'fk_' . self::TABLE_PRODUCTS_NAME . '_' . self::FIELD_PRODUCTS_FKKO_ID_NAME;

    const FK_ORG_ID_NAME = 'fk_' . self::TABLE_DOCUMENTS_NAME . '_' . self::FIELD_ORG_ID_NAME;
    const FK_ED_ID_NAME = 'fk_' . self::TABLE_DOCUMENTS_NAME . '_' . self::FIELD_ED_ID_NAME;

    const FK_TP_AUTHOR_ID_NAME = 'fk_' . self::TABLE_TP_NAME . '_' . self::FIELD_TP_AUTHOR_ID_NAME;
    const FK_TP_PRODUCT_ID_NAME = 'fk_' . self::TABLE_TP_NAME . '_' . self::FIELD_TP_PRODUCT_ID_NAME;
    const FK_TP_UNIT_ID_NAME = 'fk_' . self::TABLE_TP_NAME . '_' . self::FIELD_TP_UNIT_ID_NAME;
    const FK_TP_HK_ID_NAME = 'fk_' . self::TABLE_TP_NAME . '_' . self::FIELD_TP_HK_ID_NAME;
    const FK_TP_DC_ID_NAME = 'fk_' . self::TABLE_TP_NAME . '_' . self::FIELD_TP_DC_ID_NAME;
    const FK_TP_FKKO_ID_NAME = 'fk_' . self::TABLE_TP_NAME . '_' . self::FIELD_TP_FKKO_ID_NAME;

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // сварочные работы над таблицей товаров
        $this->renameColumn(self::TABLE_PRODUCTS_NAME, 'unit', 'src_unit');
        $this->renameColumn(self::TABLE_PRODUCTS_NAME, 'uw', 'src_uw');
        $this->renameColumn(self::TABLE_PRODUCTS_NAME, 'dc', 'src_dc');
        $this->renameColumn(self::TABLE_PRODUCTS_NAME, 'fkko', 'src_fkko');

        $this->dropForeignKey(self::FK_PRODUCTS_AUTHOR_ID_NAME, self::TABLE_PRODUCTS_NAME);
        $this->dropIndex(self::FIELD_PRODUCTS_AUTHOR_ID_NAME, self::TABLE_PRODUCTS_NAME);
        $this->dropColumn(self::TABLE_PRODUCTS_NAME, self::FIELD_PRODUCTS_AUTHOR_ID_NAME);

        $this->addColumn(self::TABLE_PRODUCTS_NAME, self::FIELD_PRODUCTS_CREATED_BY_NAME, $this->integer()->comment('Автор создания') . ' AFTER `created_at`');
        $this->createIndex(self::FIELD_PRODUCTS_CREATED_BY_NAME, self::TABLE_PRODUCTS_NAME, self::FIELD_PRODUCTS_CREATED_BY_NAME);
        $this->addForeignKey(self::FK_PRODUCTS_CREATED_BY_NAME, self::TABLE_PRODUCTS_NAME, self::FIELD_PRODUCTS_CREATED_BY_NAME, 'user', 'id');

        $this->addColumn(self::TABLE_PRODUCTS_NAME, self::FIELD_PRODUCTS_UNIT_ID_NAME, $this->integer()->comment('Единица измерения') . ' AFTER `type`');
        $this->createIndex(self::FIELD_PRODUCTS_UNIT_ID_NAME, self::TABLE_PRODUCTS_NAME, self::FIELD_PRODUCTS_UNIT_ID_NAME);
        $this->addForeignKey(self::FK_PRODUCTS_UNIT_ID_NAME, self::TABLE_PRODUCTS_NAME, self::FIELD_PRODUCTS_UNIT_ID_NAME, 'units', 'id');

        $this->addColumn(self::TABLE_PRODUCTS_NAME, self::FIELD_PRODUCTS_HK_ID_NAME, $this->integer()->comment('Вид обращения') . ' AFTER `' . self::FIELD_PRODUCTS_UNIT_ID_NAME . '`');
        $this->createIndex(self::FIELD_PRODUCTS_HK_ID_NAME, self::TABLE_PRODUCTS_NAME, self::FIELD_PRODUCTS_HK_ID_NAME);
        $this->addForeignKey(self::FK_PRODUCTS_HK_ID_NAME, self::TABLE_PRODUCTS_NAME, self::FIELD_PRODUCTS_HK_ID_NAME, 'handling_kinds', 'id');

        $this->addColumn(self::TABLE_PRODUCTS_NAME, self::FIELD_PRODUCTS_DC_ID_NAME, $this->integer()->comment('Класс опасности') . ' AFTER `' . self::FIELD_PRODUCTS_HK_ID_NAME . '`');
        $this->createIndex(self::FIELD_PRODUCTS_DC_ID_NAME, self::TABLE_PRODUCTS_NAME, self::FIELD_PRODUCTS_DC_ID_NAME);
        $this->addForeignKey(self::FK_PRODUCTS_DC_ID_NAME, self::TABLE_PRODUCTS_NAME, self::FIELD_PRODUCTS_DC_ID_NAME, 'danger_classes', 'id');

        $this->addColumn(self::TABLE_PRODUCTS_NAME, self::FIELD_PRODUCTS_FKKO_ID_NAME, $this->integer()->comment('Код ФККО') . ' AFTER `' . self::FIELD_PRODUCTS_DC_ID_NAME . '`');
        $this->createIndex(self::FIELD_PRODUCTS_FKKO_ID_NAME, self::TABLE_PRODUCTS_NAME, self::FIELD_PRODUCTS_FKKO_ID_NAME);
        $this->addForeignKey(self::FK_PRODUCTS_FKKO_ID_NAME, self::TABLE_PRODUCTS_NAME, self::FIELD_PRODUCTS_FKKO_ID_NAME, 'fkko', 'id');

        // сварочные работы над таблицей документов
        $this->addColumn(self::TABLE_DOCUMENTS_NAME, 'act_date', $this->date()->comment('Дата акта утилизации'). ' AFTER `doc_date`');

        $this->addColumn(self::TABLE_DOCUMENTS_NAME, self::FIELD_ORG_ID_NAME, $this->integer()->comment('Организация'). ' AFTER `act_date`');
        $this->createIndex(self::FIELD_ORG_ID_NAME, self::TABLE_DOCUMENTS_NAME, self::FIELD_ORG_ID_NAME);
        $this->addForeignKey(self::FK_ORG_ID_NAME, self::TABLE_DOCUMENTS_NAME, self::FIELD_ORG_ID_NAME, 'organizations', 'id');

        $this->addColumn(self::TABLE_DOCUMENTS_NAME, self::FIELD_ED_ID_NAME, $this->integer()->comment('Электронный документ'). ' AFTER `fo_contract`');
        $this->createIndex(self::FIELD_ED_ID_NAME, self::TABLE_DOCUMENTS_NAME, self::FIELD_ED_ID_NAME);
        $this->addForeignKey(self::FK_ED_ID_NAME, self::TABLE_DOCUMENTS_NAME, self::FIELD_ED_ID_NAME, 'edf', 'id');

        // сварочные работы над таблицей табличных частей документов
        $this->dropForeignKey(self::FK_TP_AUTHOR_ID_NAME, self::TABLE_TP_NAME);
        $this->dropIndex(self::FIELD_TP_AUTHOR_ID_NAME, self::TABLE_TP_NAME);
        $this->dropColumn(self::TABLE_TP_NAME, self::FIELD_TP_AUTHOR_ID_NAME);

        $this->addColumn(self::TABLE_TP_NAME, self::FIELD_TP_UNIT_ID_NAME, $this->integer()->comment('Единица измерения') . ' AFTER `quantity`');
        $this->createIndex(self::FIELD_TP_UNIT_ID_NAME, self::TABLE_TP_NAME, self::FIELD_TP_UNIT_ID_NAME);
        $this->addForeignKey(self::FK_TP_UNIT_ID_NAME, self::TABLE_TP_NAME, self::FIELD_TP_UNIT_ID_NAME, 'units', 'id');

        $this->addColumn(self::TABLE_TP_NAME, self::FIELD_TP_HK_ID_NAME, $this->integer()->comment('Вид обращения') . ' AFTER `' . self::FIELD_TP_UNIT_ID_NAME . '`');
        $this->createIndex(self::FIELD_TP_HK_ID_NAME, self::TABLE_TP_NAME, self::FIELD_TP_HK_ID_NAME);
        $this->addForeignKey(self::FK_TP_HK_ID_NAME, self::TABLE_TP_NAME, self::FIELD_TP_HK_ID_NAME, 'handling_kinds', 'id');

        $this->addColumn(self::TABLE_TP_NAME, self::FIELD_TP_DC_ID_NAME, $this->integer()->comment('Класс опасности') . ' AFTER `' . self::FIELD_TP_HK_ID_NAME . '`');
        $this->createIndex(self::FIELD_TP_DC_ID_NAME, self::TABLE_TP_NAME, self::FIELD_TP_DC_ID_NAME);
        $this->addForeignKey(self::FK_TP_DC_ID_NAME, self::TABLE_TP_NAME, self::FIELD_TP_DC_ID_NAME, 'danger_classes', 'id');

        $this->addColumn(self::TABLE_TP_NAME, self::FIELD_TP_FKKO_ID_NAME, $this->integer()->comment('Код ФККО') . ' AFTER `' . self::FIELD_TP_DC_ID_NAME . '`');
        $this->createIndex(self::FIELD_TP_FKKO_ID_NAME, self::TABLE_TP_NAME, self::FIELD_TP_FKKO_ID_NAME);
        $this->addForeignKey(self::FK_TP_FKKO_ID_NAME, self::TABLE_TP_NAME, self::FIELD_TP_FKKO_ID_NAME, 'fkko', 'id');

        $this->dropForeignKey(self::FK_TP_PRODUCT_ID_NAME, self::TABLE_TP_NAME);
        $this->dropIndex(self::FIELD_TP_PRODUCT_ID_NAME, self::TABLE_TP_NAME);
        $this->dropColumn(self::TABLE_TP_NAME, self::FIELD_TP_PRODUCT_ID_NAME);

        $this->renameColumn(self::TABLE_TP_NAME, 'dc', 'src_dc');
        $this->addColumn(self::TABLE_TP_NAME, 'src_unit', $this->string(30)->comment('Единица измерения из источника') . ' AFTER `src_dc`');
        $this->addColumn(self::TABLE_TP_NAME, 'src_uw', $this->string(50)->comment('Способ утилизации из источника') . ' AFTER `src_unit`');
        $this->addColumn(self::TABLE_TP_NAME, 'src_name', $this->string()->comment('Наименование из источника') . ' AFTER `src_uw`');
        $this->addColumn(self::TABLE_TP_NAME, 'name', $this->string()->comment('Наименование') . ' AFTER `doc_id`');

        $this->addColumn(self::TABLE_TP_NAME, 'fo_id', $this->integer(5)->comment('Код из Fresh Office') . ' AFTER `src_name`');

        $this->dropColumn(self::TABLE_TP_NAME, self::FIELD_TP_CREATED_AT_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // откат изменений в табличных частях
        $this->addColumn(self::TABLE_TP_NAME, self::FIELD_TP_CREATED_AT_NAME, $this->integer()->comment('Дата и время создания') . ' AFTER `id`');

        $this->dropColumn(self::TABLE_TP_NAME, 'fo_id');

        $this->dropColumn(self::TABLE_TP_NAME, 'name');
        $this->dropColumn(self::TABLE_TP_NAME, 'src_name');
        $this->dropColumn(self::TABLE_TP_NAME, 'src_uw');
        $this->dropColumn(self::TABLE_TP_NAME, 'src_unit');
        $this->renameColumn(self::TABLE_TP_NAME, 'src_dc', 'dc');

        $this->addColumn(self::TABLE_TP_NAME, self::FIELD_TP_PRODUCT_ID_NAME, $this->integer()->notNull()->comment('Номенклатура') . ' AFTER `doc_id`');
        $this->createIndex(self::FIELD_TP_PRODUCT_ID_NAME, self::TABLE_TP_NAME, self::FIELD_TP_PRODUCT_ID_NAME);
        $this->addForeignKey(self::FK_TP_PRODUCT_ID_NAME, self::TABLE_TP_NAME, self::FIELD_TP_PRODUCT_ID_NAME, 'products', 'id');

        $this->dropForeignKey(self::FK_TP_FKKO_ID_NAME, self::TABLE_TP_NAME);
        $this->dropIndex(self::FIELD_TP_FKKO_ID_NAME, self::TABLE_TP_NAME);
        $this->dropColumn(self::TABLE_TP_NAME, self::FIELD_TP_FKKO_ID_NAME);

        $this->dropForeignKey(self::FK_TP_DC_ID_NAME, self::TABLE_TP_NAME);
        $this->dropIndex(self::FIELD_TP_DC_ID_NAME, self::TABLE_TP_NAME);
        $this->dropColumn(self::TABLE_TP_NAME, self::FIELD_TP_DC_ID_NAME);

        $this->dropForeignKey(self::FK_TP_HK_ID_NAME, self::TABLE_TP_NAME);
        $this->dropIndex(self::FIELD_TP_HK_ID_NAME, self::TABLE_TP_NAME);
        $this->dropColumn(self::TABLE_TP_NAME, self::FIELD_TP_HK_ID_NAME);

        $this->dropForeignKey(self::FK_TP_UNIT_ID_NAME, self::TABLE_TP_NAME);
        $this->dropIndex(self::FIELD_TP_UNIT_ID_NAME, self::TABLE_TP_NAME);
        $this->dropColumn(self::TABLE_TP_NAME, self::FIELD_TP_UNIT_ID_NAME);

        $this->addColumn(self::TABLE_TP_NAME, self::FIELD_TP_AUTHOR_ID_NAME, $this->integer()->notNull()->comment('Автор создания записи') . ' AFTER `created_at`');
        $this->createIndex(self::FIELD_TP_AUTHOR_ID_NAME, self::TABLE_TP_NAME, self::FIELD_TP_AUTHOR_ID_NAME);
        $this->addForeignKey(self::FK_TP_AUTHOR_ID_NAME, self::TABLE_TP_NAME, self::FIELD_TP_AUTHOR_ID_NAME, 'user', 'id');

        // откат изменений в документах
        $this->dropForeignKey(self::FK_ED_ID_NAME, self::TABLE_DOCUMENTS_NAME);
        $this->dropIndex(self::FIELD_ED_ID_NAME, self::TABLE_DOCUMENTS_NAME);
        $this->dropColumn(self::TABLE_DOCUMENTS_NAME, self::FIELD_ED_ID_NAME);

        $this->dropForeignKey(self::FK_ORG_ID_NAME, self::TABLE_DOCUMENTS_NAME);
        $this->dropIndex(self::FIELD_ORG_ID_NAME, self::TABLE_DOCUMENTS_NAME);
        $this->dropColumn(self::TABLE_DOCUMENTS_NAME, self::FIELD_ORG_ID_NAME);

        $this->dropColumn(self::TABLE_DOCUMENTS_NAME, 'act_date');

        // откат изменений в товарах
        $this->dropForeignKey(self::FK_PRODUCTS_FKKO_ID_NAME, self::TABLE_PRODUCTS_NAME);
        $this->dropIndex(self::FIELD_PRODUCTS_FKKO_ID_NAME, self::TABLE_PRODUCTS_NAME);
        $this->dropColumn(self::TABLE_PRODUCTS_NAME, self::FIELD_PRODUCTS_FKKO_ID_NAME);

        $this->dropForeignKey(self::FK_PRODUCTS_DC_ID_NAME, self::TABLE_PRODUCTS_NAME);
        $this->dropIndex(self::FIELD_PRODUCTS_DC_ID_NAME, self::TABLE_PRODUCTS_NAME);
        $this->dropColumn(self::TABLE_PRODUCTS_NAME, self::FIELD_PRODUCTS_DC_ID_NAME);

        $this->dropForeignKey(self::FK_PRODUCTS_HK_ID_NAME, self::TABLE_PRODUCTS_NAME);
        $this->dropIndex(self::FIELD_PRODUCTS_HK_ID_NAME, self::TABLE_PRODUCTS_NAME);
        $this->dropColumn(self::TABLE_PRODUCTS_NAME, self::FIELD_PRODUCTS_HK_ID_NAME);

        $this->dropForeignKey(self::FK_PRODUCTS_UNIT_ID_NAME, self::TABLE_PRODUCTS_NAME);
        $this->dropIndex(self::FIELD_PRODUCTS_UNIT_ID_NAME, self::TABLE_PRODUCTS_NAME);
        $this->dropColumn(self::TABLE_PRODUCTS_NAME, self::FIELD_PRODUCTS_UNIT_ID_NAME);

        $this->dropForeignKey(self::FK_PRODUCTS_CREATED_BY_NAME, self::TABLE_PRODUCTS_NAME);
        $this->dropIndex(self::FIELD_PRODUCTS_CREATED_BY_NAME, self::TABLE_PRODUCTS_NAME);
        $this->dropColumn(self::TABLE_PRODUCTS_NAME, self::FIELD_PRODUCTS_CREATED_BY_NAME);

        $this->addColumn(self::TABLE_PRODUCTS_NAME, self::FIELD_PRODUCTS_AUTHOR_ID_NAME, $this->integer()->notNull()->comment('Автор создания записи') . ' AFTER `is_deleted`');
        $this->createIndex(self::FIELD_PRODUCTS_AUTHOR_ID_NAME, self::TABLE_PRODUCTS_NAME, self::FIELD_PRODUCTS_AUTHOR_ID_NAME);
        $this->addForeignKey(self::FK_PRODUCTS_AUTHOR_ID_NAME, self::TABLE_PRODUCTS_NAME, self::FIELD_PRODUCTS_AUTHOR_ID_NAME, 'user', 'id');

        $this->renameColumn(self::TABLE_PRODUCTS_NAME, 'src_unit', 'unit');
        $this->renameColumn(self::TABLE_PRODUCTS_NAME, 'src_uw', 'uw');
        $this->renameColumn(self::TABLE_PRODUCTS_NAME, 'src_dc', 'dc');
        $this->renameColumn(self::TABLE_PRODUCTS_NAME, 'src_fkko', 'fkko');
    }
}
