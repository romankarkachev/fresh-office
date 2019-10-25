<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "po_ei".
 *
 * @property int $id
 * @property int $group_id Группа статей
 * @property string $name Наименование
 *
 * @property PoEig $group
 * @property PoEip[] $poEips
 */
class PoEi extends \yii\db\ActiveRecord
{
    const СТАТЬЯ_ПЕРЕВОЗЧИКИ = 93;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'po_ei';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['group_id', 'name'], 'required'],
            [['group_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['group_id'], 'exist', 'skipOnError' => true, 'targetClass' => PoEig::class, 'targetAttribute' => ['group_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_id' => 'Группа статей',
            'name' => 'Наименование',
            // вычисляемые поля
            'groupName' => 'Группа',
        ];
    }

    /**
     * Выполняет проверку, используется ли запись в других элементах.
     * @return bool
     */
    public function checkIfUsed()
    {
        if ($this->getPoEips()->count() > 0) return true;

        return false;
    }

    /**
     * Делает выборку статей расходов и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapForSelect2()
    {
        return ArrayHelper::map(self::find()->all(), 'id', 'name');
    }

    /**
     * Делает выборку статей расходов по группам и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @return array
     */
    public static function arrayMapByGroupsForSelect2()
    {
        $query = self::find()->select([
            self::tableName() . '.id',
            self::tableName() . '.name',
            'groupName' => PoEig::tableName() . '.name',
        ])->joinWith(['group'])->orderBy(PoEig::tableName() . '.name, ' . self::tableName() . '.name');

        // дополним запрос условием отбора только лишь доступных статей, если они заданы
        if (!Yii::$app->user->can('root')) {
            // это не распространяется на пользователей с полными правами
            $query->where([self::tableName() . '.id' => UsersEiAccess::find()->select('ei_id')->where(['user_id' => Yii::$app->user->id])]);
        }

        $array = $query->asArray()->all();
        if (empty($array)) return [];

        // обработаем массив, чтобы можно было его использовать в виджете select2 с группами
        $result = [];
        $current_group = -1;
        $children = [];
        $prev_name = '';
        foreach ($array as $type) {
            if ($type['groupName'] != $current_group && $current_group != -1) {
                $result[$prev_name] = $children;
                $children = [];
            }
            $prev_name = $type['groupName'];
            $children[$type['id']] = $type['name'];
            $current_group = $type['groupName'];
        }
        if (count($children) > 0) {
            $result[$prev_name] = $children;
        }

        return $result;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(PoEig::className(), ['id' => 'group_id']);
    }

    /**
     * Возвращает наименование группы статей.
     * @return string
     */
    public function getGroupName()
    {
        return !empty($this->group) ? $this->group->name : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPoEips()
    {
        return $this->hasMany(PoEip::className(), ['ei_id' => 'id']);
    }
}
