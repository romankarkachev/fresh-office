<?php

use yii\db\Migration;

/**
 * Создается таблица "Тендеры".
 */
class m190615_092214_create_tenders_table extends Migration
{
    /**
     * Поля, которые имеют внешние ключи
     */
    const FIELD_CREATED_BY_NAME = 'created_by';
    const FIELD_ORG_ID_NAME = 'org_id';
    const FIELD_TP_ID_NAME = 'tp_id';
    const FIELD_MANAGER_ID_NAME = 'manager_id';
    const FIELD_RESPONSIBLE_ID_NAME = 'responsible_id';
    const FIELD_TA_ID_NAME = 'ta_id';
    const FIELD_LR_ID_NAME = 'lr_id';

    /**
     * @var string наименование таблицы, которая добавляется
     */
    private $tableName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля created_by
     */
    private $fkCreatedByName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля org_id
     */
    private $fkOrgIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля tp_id
     */
    private $fkTpIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля manager_id
     */
    private $fkManagerIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля responsible_id
     */
    private $fkResponsibleIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля ta_id
     */
    private $fkTaIdName;

    /**
     * @var string наименование внешнего ключа для добавляемого поля lr_id
     */
    private $fkLrIdName;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->tableName = 'tenders';
        $this->fkCreatedByName = 'fk_' . $this->tableName . '_' . self::FIELD_CREATED_BY_NAME;
        $this->fkOrgIdName = 'fk_' . $this->tableName . '_' . self::FIELD_ORG_ID_NAME;
        $this->fkTpIdName = 'fk_' . $this->tableName . '_' . self::FIELD_TP_ID_NAME;
        $this->fkManagerIdName = 'fk_' . $this->tableName . '_' . self::FIELD_MANAGER_ID_NAME;
        $this->fkResponsibleIdName = 'fk_' . $this->tableName . '_' . self::FIELD_RESPONSIBLE_ID_NAME;
        $this->fkTaIdName = 'fk_' . $this->tableName . '_' . self::FIELD_TA_ID_NAME;
        $this->fkLrIdName = 'fk_' . $this->tableName . '_' . self::FIELD_LR_ID_NAME;

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB COMMENT "Тендеры"';
        };

        $this->createTable($this->tableName, [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer()->notNull()->comment('Дата и время создания'),
            self::FIELD_CREATED_BY_NAME => $this->integer()->notNull()->comment('Автор создания'),
            'title' => $this->text()->comment('Наименование закупки'),
            self::FIELD_ORG_ID_NAME => $this->integer()->notNull()->comment('Организация'),
            'fo_ca_id' => $this->integer()->notNull()->comment('Контрагент'),
            'fo_ca_name' => $this->string()->comment('Наименование контрагента из Fresh office'),
            self::FIELD_TP_ID_NAME => $this->integer()->notNull()->comment('Тендерная площадка'),
            self::FIELD_MANAGER_ID_NAME => $this->integer()->notNull()->comment('Ответственный менеджер'),
            self::FIELD_RESPONSIBLE_ID_NAME => $this->integer()->comment('Исполнитель'),
            'conditions' => $this->text()->notNull()->comment('Особые требования'),
            'date_complete' => $this->date()->notNull()->comment('Срок выполнения работ (услуг)'),
            'date_stop' => $this->date()->comment('Дата окончания приема заявок'),
            self::FIELD_TA_ID_NAME => $this->integer()->notNull()->comment('Форма подачи'),
            'is_notary_required' => 'TINYINT(1) NOT NULL DEFAULT"0" COMMENT"Требуется ли нотариальное заверение документов"',
            'is_contract_edit' => 'TINYINT(1) NOT NULL DEFAULT"0" COMMENT"Возможно ли внесение изменений в текст договора"',
            'amount_start' => $this->decimal(12,2)->notNull()->comment('Начальная максимальная цена'),
            'amount_offer' => $this->decimal(12,2)->notNull()->comment('Наше ценовое предложение'),
            'deferral' => 'TINYINT(1) NOT NULL DEFAULT"0" COMMENT"Срок оплаты (количество дней отсрочки платежа)"',
            'is_contract_approved' => 'TINYINT(1) NOT NULL DEFAULT"0" COMMENT"Договор согласован (0 - нет, 1 - да, 2 - на согласовании)"',
            self::FIELD_LR_ID_NAME => $this->integer()->comment('Запрос лицензий'),
            'comment' => $this->text()->comment('Комментарий'),
        ], $tableOptions);

        $this->createIndex(self::FIELD_CREATED_BY_NAME, $this->tableName, self::FIELD_CREATED_BY_NAME);
        $this->createIndex(self::FIELD_ORG_ID_NAME, $this->tableName, self::FIELD_ORG_ID_NAME);
        $this->createIndex(self::FIELD_TP_ID_NAME, $this->tableName, self::FIELD_TP_ID_NAME);
        $this->createIndex(self::FIELD_MANAGER_ID_NAME, $this->tableName, self::FIELD_MANAGER_ID_NAME);
        $this->createIndex(self::FIELD_RESPONSIBLE_ID_NAME, $this->tableName, self::FIELD_RESPONSIBLE_ID_NAME);
        $this->createIndex(self::FIELD_TA_ID_NAME, $this->tableName, self::FIELD_TA_ID_NAME);
        $this->createIndex(self::FIELD_LR_ID_NAME, $this->tableName, self::FIELD_LR_ID_NAME);

        $this->addForeignKey($this->fkCreatedByName, $this->tableName, self::FIELD_CREATED_BY_NAME, 'user', 'id');
        $this->addForeignKey($this->fkOrgIdName, $this->tableName, self::FIELD_ORG_ID_NAME, 'organizations', 'id');
        $this->addForeignKey($this->fkTpIdName, $this->tableName, self::FIELD_TP_ID_NAME, 'tenders_platforms', 'id');
        $this->addForeignKey($this->fkManagerIdName, $this->tableName, self::FIELD_MANAGER_ID_NAME, 'user', 'id');
        $this->addForeignKey($this->fkResponsibleIdName, $this->tableName, self::FIELD_RESPONSIBLE_ID_NAME, 'user', 'id');
        $this->addForeignKey($this->fkTaIdName, $this->tableName, self::FIELD_TA_ID_NAME, 'tenders_applications', 'id');
        $this->addForeignKey($this->fkLrIdName, $this->tableName, self::FIELD_LR_ID_NAME, 'licenses_requests', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey($this->fkLrIdName, $this->tableName);
        $this->dropForeignKey($this->fkTaIdName, $this->tableName);
        $this->dropForeignKey($this->fkResponsibleIdName, $this->tableName);
        $this->dropForeignKey($this->fkManagerIdName, $this->tableName);
        $this->dropForeignKey($this->fkTpIdName, $this->tableName);
        $this->dropForeignKey($this->fkOrgIdName, $this->tableName);
        $this->dropForeignKey($this->fkCreatedByName, $this->tableName);

        $this->dropIndex(self::FIELD_LR_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_TA_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_RESPONSIBLE_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_MANAGER_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_TP_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_ORG_ID_NAME, $this->tableName);
        $this->dropIndex(self::FIELD_CREATED_BY_NAME, $this->tableName);

        $this->dropTable($this->tableName);
    }
}
