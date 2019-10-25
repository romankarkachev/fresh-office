<?php

use yii\db\Migration;

/**
 * Создается таблица для ведения документооборота.
 */
class m181016_152933_create_edf_table extends Migration
{
    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_CREATED_BY_NAME = 'created_by';
    const FIELD_TYPE_ID_NAME = 'type_id';
    const FIELD_PARENT_ID_NAME = 'parent_id';
    const FIELD_CT_ID_NAME = 'ct_id';
    const FIELD_STATE_ID_NAME = 'state_id';
    const FIELD_ORG_ID_NAME = 'org_id';
    const FIELD_BA_ID_NAME = 'ba_id';
    const FIELD_MANAGER_ID_NAME = 'manager_id';
    const FIELD_CP_ID_NAME = 'cp_id';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля created_by
     */
    private $fkCreatedByName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля type_id
     */
    private $fkTypeIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля parent_id
     */
    private $fkParentIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля ct_id
     */
    private $fkCtIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля state_id
     */
    private $fkStateIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля org_id
     */
    private $fkOrgIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля ba_id
     */
    private $fkBaIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля manager_id
     */
    private $fkManagerIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля cp_id
     */
    private $fkCpIdName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'edf';
        $this->fkCreatedByName = 'fk_' . $this->tableName . '_' . self::FIELD_CREATED_BY_NAME;
        $this->fkTypeIdName = 'fk_' . $this->tableName . '_' . self::FIELD_TYPE_ID_NAME;
        $this->fkParentIdName = 'fk_' . $this->tableName . '_' . self::FIELD_PARENT_ID_NAME;
        $this->fkCtIdName = 'fk_' . $this->tableName . '_' . self::FIELD_CT_ID_NAME;
        $this->fkStateIdName = 'fk_' . $this->tableName . '_' . self::FIELD_STATE_ID_NAME;
        $this->fkOrgIdName = 'fk_' . $this->tableName . '_' . self::FIELD_ORG_ID_NAME;
        $this->fkBaIdName = 'fk_' . $this->tableName . '_' . self::FIELD_BA_ID_NAME;
        $this->fkManagerIdName = 'fk_' . $this->tableName . '_' . self::FIELD_MANAGER_ID_NAME;
        $this->fkCpIdName = 'fk_' . $this->tableName . '_' . self::FIELD_CP_ID_NAME;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Электронный документооборот (electronic document flow)"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            self::FIELD_CREATED_BY_NAME => $this->integer()->notNull()->comment('Автор создания'),
            self::FIELD_TYPE_ID_NAME => $this->integer()->notNull()->comment('Тип документа'),
            self::FIELD_PARENT_ID_NAME => $this->integer()->comment('Корневой документ (договор к допсоглашению, например)'),
            self::FIELD_CT_ID_NAME => $this->integer()->comment('Вид договора'),
            self::FIELD_STATE_ID_NAME => $this->integer()->notNull()->comment('Статус документа'),
            self::FIELD_ORG_ID_NAME => $this->integer()->notNull()->comment('Организация'),
            self::FIELD_BA_ID_NAME => $this->integer()->comment('Банковский счет'),
            self::FIELD_MANAGER_ID_NAME => $this->integer()->notNull()->comment('Ответственный менеджер'),
            self::FIELD_CP_ID_NAME => $this->integer()->comment('Пакет корреспонденции'),
            'fo_ca_id' => $this->integer()->comment('Контрагент из Fresh Office'),
            'is_typical_form' => 'TINYINT(1) NOT NULL DEFAULT"0" COMMENT"Типовая форма документа"',
            'doc_num' => $this->string()->notNull()->comment('Номер документа'),
            'doc_date' => $this->date()->notNull()->comment('Дата документа'),
            'doc_date_expires' => $this->date()->comment('Дата окончания документа'),
            'basis' => $this->string()->comment('Основание'),
            'req_name_full' => $this->string()->comment('Полное наименование контрагента'),
            'req_name_short' => $this->string()->comment('Сокращенное наименование контрагента'),
            'req_ogrn' => $this->string()->comment('ОГРН контрагента'),
            'req_inn' => $this->string()->comment('ИНН контрагента'),
            'req_kpp' => $this->string()->comment('КПП контрагента'),
            'req_address_j' => $this->string()->comment('Юридический адрес контрагента'),
            'req_address_f' => $this->string()->comment('Фактический адрес контрагента'),
            'req_an' => $this->string(25)->comment('Номер банковского счета контрагента'),
            'req_bik' => $this->string(10)->comment('БИК банка контрагента'),
            'req_bn' => $this->string()->comment('Наименование банка контрагента'),
            'req_ca' => $this->string(25)->comment('Корреспондентский счет контрагента'),
            'req_phone' => $this->string()->comment('Номер телефона контрагента'),
            'req_email' => $this->string()->comment('E-mail контрагента'),
            'req_dir_post' => $this->string()->comment('Должность директора контрагента (им. падеж)'),
            'req_dir_name' => $this->string()->comment('ФИО директора контрагента полностью (им. падеж)'),
            'req_dir_name_of' => $this->string()->comment('ФИО директора контрагента полностью (род. падеж)'),
            'req_dir_name_short' => $this->string()->comment('ФИО директора контрагента сокрщенно (им. падеж)'),
            'req_dir_name_short_of' => $this->string()->comment('ФИО директора контрагента сокращенно (род. падеж)'),
            'is_received_scan' => 'TINYINT(1) NOT NULL DEFAULT"0" COMMENT"Скан-копии получены"',
            'is_received_original' => 'TINYINT(1) NOT NULL DEFAULT"0" COMMENT"Оригинал сдан в бухгалтерию"',
            'files_full_path' => $this->text()->comment('Полный путь к папке для хранения файлов электронного документа'),
            'comment' => $this->text()->comment('Комментарий'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_CREATED_BY_NAME, $this->tableName, self::FIELD_CREATED_BY_NAME);
        $this->createIndex(self::FIELD_TYPE_ID_NAME, $this->tableName, self::FIELD_TYPE_ID_NAME);
        $this->createIndex(self::FIELD_PARENT_ID_NAME, $this->tableName, self::FIELD_PARENT_ID_NAME);
        $this->createIndex(self::FIELD_CT_ID_NAME, $this->tableName, self::FIELD_CT_ID_NAME);
        $this->createIndex(self::FIELD_STATE_ID_NAME, $this->tableName, self::FIELD_STATE_ID_NAME);
        $this->createIndex(self::FIELD_ORG_ID_NAME, $this->tableName, self::FIELD_ORG_ID_NAME);
        $this->createIndex(self::FIELD_BA_ID_NAME, $this->tableName, self::FIELD_BA_ID_NAME);
        $this->createIndex(self::FIELD_MANAGER_ID_NAME, $this->tableName, self::FIELD_MANAGER_ID_NAME);
        $this->createIndex(self::FIELD_CP_ID_NAME, $this->tableName, self::FIELD_CP_ID_NAME);

        $this->addForeignKey($this->fkCreatedByName, $this->tableName, self::FIELD_CREATED_BY_NAME, 'user', 'id');
        $this->addForeignKey($this->fkTypeIdName, $this->tableName, self::FIELD_TYPE_ID_NAME, 'documents_types', 'id');
        $this->addForeignKey($this->fkParentIdName, $this->tableName, self::FIELD_PARENT_ID_NAME, $this->tableName, 'id');
        $this->addForeignKey($this->fkCtIdName, $this->tableName, self::FIELD_CT_ID_NAME, 'contract_types', 'id');
        $this->addForeignKey($this->fkStateIdName, $this->tableName, self::FIELD_STATE_ID_NAME, 'edf_states', 'id');
        $this->addForeignKey($this->fkOrgIdName, $this->tableName, self::FIELD_ORG_ID_NAME, 'organizations', 'id');
        $this->addForeignKey($this->fkBaIdName, $this->tableName, self::FIELD_BA_ID_NAME, 'organizations_bas', 'id');
        $this->addForeignKey($this->fkManagerIdName, $this->tableName, self::FIELD_MANAGER_ID_NAME, 'user', 'id');
        $this->addForeignKey($this->fkCpIdName, $this->tableName, self::FIELD_CP_ID_NAME, 'correspondence_packages', 'id');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropForeignKey($this->fkCpIdName, $this->tableName);
        $this->dropForeignKey($this->fkManagerIdName, $this->tableName);
        $this->dropForeignKey($this->fkBaIdName, $this->tableName);
        $this->dropForeignKey($this->fkOrgIdName, $this->tableName);
        $this->dropForeignKey($this->fkStateIdName, $this->tableName);
        $this->dropForeignKey($this->fkCtIdName, $this->tableName);
        $this->dropForeignKey($this->fkParentIdName, $this->tableName);
        $this->dropForeignKey($this->fkTypeIdName, $this->tableName);
        $this->dropForeignKey($this->fkCreatedByName, $this->tableName);

        $this->dropIndex(self::FIELD_CP_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_MANAGER_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_BA_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_ORG_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_STATE_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_CT_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_PARENT_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_TYPE_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_CREATED_BY_NAME, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
