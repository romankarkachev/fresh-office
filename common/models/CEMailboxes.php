<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "ce_mailboxes".
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $created_by
 * @property string $name
 * @property integer $type_id
 * @property integer $category_id
 * @property integer $is_active
 * @property string $host
 * @property string $username
 * @property string $password
 * @property string $port
 * @property integer $is_ssl
 * @property integer $is_primary_done
 *
 * @property integer $messagesCount
 * @property string $typeName
 * @property string $categoryName
 *
 * @property CEMailboxesTypes $type
 * @property CEMailboxesCategories $category
 * @property User $createdBy
 * @property CEMessages[] $messages
 * @property CEUsersAccess[] $usersAccesses
 */
class CEMailboxes extends \yii\db\ActiveRecord
{
    /**
     * @var integer количество писем в ящике в рамках текущей базы данных
     */
    public $messagesCount;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ce_mailboxes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type_id'], 'required'],
            [['created_at', 'created_by', 'type_id', 'category_id', 'is_active', 'is_ssl', 'is_primary_done'], 'integer'],
            [['name', 'host', 'username'], 'string', 'max' => 255],
            [['password'], 'string', 'max' => 50],
            [['port'], 'string', 'max' => 6],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => CEMailboxesTypes::className(), 'targetAttribute' => ['type_id' => 'id']],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => CEMailboxesCategories::className(), 'targetAttribute' => ['category_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Дата и время создания',
            'created_by' => 'Создатель',
            'name' => 'Наименование',
            'type_id' => 'Тип',
            'category_id' => 'Категория',
            'is_active' => 'Активен', // 0 - сбор писем не производится, 1 - производится
            'host' => 'Хост',
            'username' => 'Имя пользователя',
            'password' => 'Пароль',
            'port' => 'Порт',
            'is_ssl' => 'Применят ли ssl',
            'is_primary_done' => 'Первичный сбор завершен',
            // вычисляемые поля
            'typeName' => 'Тип',
            'categoryName' => 'Категория',
            'messagesCount' => 'Количество писем',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
            'blameable' => [
                'class' => 'yii\behaviors\BlameableBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_by'],
                ],
            ],
        ];
    }

    /**
     * Выполняет проверку, используется ли запись в других элементах.
     * @return bool
     */
    public function checkIfUsed()
    {
        if ($this->getMessagesCount() > 0) return true;

        return false;
    }

    /**
     * Делает выборку корпоративных почтовых ящиков и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::find()->orderBy('name')->all(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(CEMailboxesTypes::className(), ['id' => 'type_id']);
    }

    /**
     * Возвращает тип почтового ящика.
     * @return string
     */
    public function getTypeName()
    {
        return (!empty($this->type) ? $this->type->name : '');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(CEMailboxesCategories::className(), ['id' => 'category_id']);
    }

    /**
     * Возвращает наименование категории почтового ящика.
     * @return string
     */
    public function getCategoryName()
    {
        return (!empty($this->category) ? $this->category->name : '');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessages()
    {
        return $this->hasMany(CEMessages::className(), ['mailbox_id' => 'id']);
    }

    /**
     * Возвращает количество скачанных писем в этот ящик.
     * @return integer
     */
    public function getMessagesCount2()
    {
        return $this->hasMany(CEMessages::className(), ['mailbox_id' => 'id'])->where(['is_complete' => true])->count();
    }
}
