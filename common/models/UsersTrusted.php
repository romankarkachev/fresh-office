<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Таблица "Доверенные лица пользователей".
 *
 * @property int $id
 * @property int $user_id Пользователь
 * @property int $section Раздел учета
 * @property int $trusted_id Доверенное лицо
 *
 * @property string $sectionName
 * @property string $userProfileName
 * @property string $trustedProfileName
 *
 * @property User $user
 * @property Profile $userProfile
 * @property User $trusted
 * @property Profile $trustedProfile
 */
class UsersTrusted extends \yii\db\ActiveRecord
{
    /**
     * Возможные значения для поля "Раздел учета"
     */
    const SECTION_ПАКЕТЫ_КОРРЕСПОНДЕНЦИИ = 1;
    const SECTION_ДОКУМЕНТООБОРОТ = 2;
    const SECTION_ТЕНДЕРЫ = 3;
    const SECTION_ЗАДАЧИ = 4;
    const SECTION_ЭКО_ДОГОВОРЫ = 5;
    const SECTION_ЭКО_ПРОЕКТЫ = 6;
    const SECTION_ПО_БЮДЖЕТ = 7;
    const SECTION_КОНТРАГЕНТЫ = 8;

    /**
     * Идентификаторы элементов страницы
     */
    const DOM_IDS = [
        // форма для интерактивного добавления доверенного лица
        'PJAX_FORM_ID' => 'frmNewTrusted',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users_trusted';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'section', 'trusted_id'], 'required'],
            [['user_id', 'section', 'trusted_id'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['trusted_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['trusted_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'Пользователь',
            'section' => 'Раздел учета',
            'trusted_id' => 'Доверенное лицо',
            // вычисляемые поля
            'userProfileName' => 'Пользователь',
            'sectionName' => 'Раздел учета',
            'trustedProfileName' => 'Доверенное лицо',
        ];
    }

    /**
     * Возвращает разделы учета, доступные в этом справочнике.
     * @return array
     */
    public static function fetchSections()
    {
        return [
            [
                'id' => self::SECTION_ПАКЕТЫ_КОРРЕСПОНДЕНЦИИ,
                'name' => 'Пакеты корреспонденции',
            ],
            [
                'id' => self::SECTION_ДОКУМЕНТООБОРОТ,
                'name' => 'Документооборот',
            ],
            [
                'id' => self::SECTION_ТЕНДЕРЫ,
                'name' => 'Тендеры',
            ],
            [
                'id' => self::SECTION_ЗАДАЧИ,
                'name' => 'Задачи',
            ],
            [
                'id' => self::SECTION_ЭКО_ДОГОВОРЫ,
                'name' => 'Эко отчеты',
            ],
            [
                'id' => self::SECTION_ЭКО_ПРОЕКТЫ,
                'name' => 'Эко проекты',
            ],
            [
                'id' => self::SECTION_ПО_БЮДЖЕТ,
                'name' => 'Бюджетные ордеры',
            ],
            [
                'id' => self::SECTION_КОНТРАГЕНТЫ,
                'name' => 'Контрагенты',
            ],
        ];
    }

    /**
     * Делает выборку разделов учета и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::fetchSections(), 'id', 'name');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserProfile()
    {
        return $this->hasOne(Profile::class, ['user_id' => 'user_id'])->from(['userProfile' => 'profile']);
    }

    /**
     * Возвращает имя создателя записи.
     * @return string
     */
    public function getUserProfileName()
    {
        return !empty($this->userProfile) ? (!empty($this->userProfile->name) ? $this->userProfile->name : $this->user->username) : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTrusted()
    {
        return $this->hasOne(User::class, ['id' => 'trusted_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTrustedProfile()
    {
        return $this->hasOne(Profile::class, ['user_id' => 'trusted_id'])->from(['trustedProfile' => 'profile']);
    }

    /**
     * Возвращает имя создателя записи.
     * @return string
     */
    public function getTrustedProfileName()
    {
        return !empty($this->trustedProfile) ? (!empty($this->trustedProfile->name) ? $this->trustedProfile->name : $this->trusted->username) : '';
    }

    /**
     * Возвращает наименование раздела учета.
     * @return string
     */
    public function getSectionName()
    {
        if (!empty($this->section)) {
            $sourceTable = self::fetchSections();
            $key = array_search($this->section, array_column($sourceTable, 'id'));
            if (false !== $key) return $sourceTable[$key]['name'];
        }

        return '';
    }
}
