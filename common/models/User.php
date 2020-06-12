<?php

namespace common\models;

use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use dektrium\user\helpers\Password;
use dektrium\user\models\User as BaseUser;

/**
 * @property string $roleName
 * @property float $advanceBalanceStored
 * @property float $advanceBalanceCalculated
 *
 * @property AuthItem $role
 * @property Profile $profile
 * @property EcoProjects $ecoProjects
 * @property EcoProjectsAccess $ecoProjectsAccesses
 * @property EcoProjectsMilestonesFiles $ecoProjectsMilestonesFiles
 */
class User extends BaseUser
{
    /**
     * Префикс для имен пользователей. Добавляется при регистрации и при каждой авторизации пользователя в роли
     * перевозчика.
     */
    const FERRYMAN_LOGIN_PREFIX = 'ferryman';

    /**
     * Признаки, определяющие способ отбора пользователей в зависимости от роли
     */
    const ARRAY_MAP_OF_USERS_BY_ALL_ROLES = 1; // все пользователи
    const ARRAY_MAP_OF_ALL_EXCEPT_SELF = 7; // все пользователи, кроме себя самого
    const ARRAY_MAP_OF_ALL_EXCEPT_SPECIAL = 8; // все пользователи, кроме переданных
    const ARRAY_MAP_OF_USERS_BY_MANAGER_ROLE = 2; // отбор по роли менеджера
    const ARRAY_MAP_OF_USERS_BY_ECOLOGIST_ROLE = 3; // только экологи
    const ARRAY_MAP_OF_USERS_BY_MANAGER_AND_ECOLOGIST_ROLE = 4; // только менеджеры и экологи
    const ARRAY_MAP_OF_USERS_BY_LOGIST_ROLE = 5; // только логисты
    const USERS_ALL_JOIN_FINANCE_BALANCE = 6; // все пользователи с балансом по подотчету
    const USERS_ALL_WEB_APP = 9; // все пользователи, имеющие непосредственное отношение к веб-приложению (кроме водителей, перевозчиков, заказчиков)
    const USERS_TENDERS = 10; // специалисты отдела тендеров
    const USERS_FO_ATTACHED = 11; // все пользователи, которые имеют привязку к CRM Fresh Office

    /**
     * Имя.
     * @var string
     */
    public $name;

    /**
     * Идентификатор пользователя во Fresh Office.
     * @var integer
     */
    public $fo_id;

    /**
     * Роль.
     * @var string
     */
    public $role_id;

    /**
     * @var array отделы, к которым относится пользователь
     */
    public $departments;

    /**
     * @var array статьи расходов, доступные пользователю
     */
    public $poEis;

    /**
     * Подтверждение пароля.
     * @var string
     */
    public $password_confirm;

    /**
     * ФИО пользователя (для вложенного запроса и сортировки).
     */
    public $profileName;

    /**
     * Описание роли пользователя (для вложенного запроса и сортировки).
     * @var string
     */
    public $roleName;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules[] = [['name', 'role_id'], 'required', 'on' => 'create'];
        $rules[] = [['username', 'email'], 'required', 'on' => 'update'];
        $rules[] = [['fo_id'], 'integer'];
        $rules[] = [['role_id'], 'string'];
        $rules['password_confirm'] = ['password', 'string', 'min' => 6];
        $rules[] = ['password_confirm', 'required', 'on' => 'create'];
        $rules[] = ['password_confirm', 'compare', 'skipOnEmpty' => false, 'compareAttribute' => 'password', 'message' => 'Пароли не совпадают', 'on' => 'create'];
        $rules[] = [['name'], 'string', 'max' => 255];
        $rules[] = [['departments', 'poEis'], 'safe'];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'email' => 'E-mail',
            'fo_id' => 'Пользователь Fresh Office',
            'name' => 'ФИО',
            'role_id' => 'Роль',
            'departments' => 'Отделы',
            'poEis' => 'Доступные статьи расходов',
            'password_confirm' => 'Подтверждение пароля',
            // для сортировки
            'profileName' => 'ФИО',
            'roleName' => 'Роль',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            // удаляем привязку к перевозчику
            Ferrymen::updateAll(['user_id' => null], ['user_id' => $this->id]);

            // удаляем приглашение клиента
            CustomerInvitations::deleteAll(['user_id' => $this->id]);

            // очищаем поле updated_by таблицы ferrymen
            Ferrymen::updateAll(['updated_by' => null], ['updated_by' => $this->id]);

            // очищаем поля created_by и updated_by таблицы drivers
            Drivers::updateAll(['created_by' => null], ['created_by' => $this->id]);
            Drivers::updateAll(['updated_by' => null], ['updated_by' => $this->id]);

            // очищаем поля created_by и updated_by таблицы transport
            Transport::updateAll(['created_by' => null], ['created_by' => $this->id]);
            Transport::updateAll(['updated_by' => null], ['updated_by' => $this->id]);

            // очищаем поле user_id таблицы drivers
            Drivers::updateAll(['user_id' => null], ['user_id' => $this->id]);

            // делаем автором загрузки файлов встроенного пользователя
            DriversFiles::updateAll(['uploaded_by' => 1], ['uploaded_by' => $this->id]);
            TransportFiles::updateAll(['uploaded_by' => 1], ['uploaded_by' => $this->id]);

            // удаляем отделы, в которых состоит пользователь
            UsersDepartments::deleteAll(['user_id' => $this->id]);

            // удаляем записи о том, кому доверял разделы учета этот пользователь, и о том, кто ему доверял
            UsersTrusted::deleteAll([
                'or',
                ['user_id' => $this->id],
                ['trusted_id' => $this->id],
            ]);

            // удаляем записи о статьях расходов, к которым имел доступ пользователь
            UsersEiAccess::deleteAll(['user_id' => $this->id]);

            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Creates new user account. It generates password if it is not provided by user.
     *
     * @return bool
     */
    public function create()
    {
        if ($this->getIsNewRecord() == false) {
            throw new \RuntimeException('Calling "' . __CLASS__ . '::' . __METHOD__ . '" on existing user');
        }

        $this->confirmed_at = time();
        $this->password = $this->password == null ? Password::generate(8) : $this->password;

        $this->trigger(self::BEFORE_CREATE);

        if (!$this->save()) {
            return false;
        }

        $this->trigger(self::AFTER_CREATE);

        // задание роли
        $role = Yii::$app->authManager->getRole($this->role_id);
        Yii::$app->authManager->assign($role, $this->getId());

        // заполнение профиля
        $this->profile->name = $this->name;
        $this->profile->fo_id = $this->fo_id;
        $this->profile->save();

        // заполнение отделов, к которым относится пользователь
        if (!empty($this->departments)) {
            UsersDepartments::deleteAll(['user_id' => $this->id]);

            foreach ($this->departments as $department) {
                (new UsersDepartments([
                    'user_id' => $this->id,
                    'department_id' => $department,
                ]))->save();
            }
        }

        // заполнение статей расходов, к которым имеют доступ сотрудники
        UsersEiAccess::deleteAll(['user_id' => $this->id]);
        if (!empty($this->poEis)) {
            foreach ($this->poEis as $ei) {
                (new UsersEiAccess([
                    'user_id' => $this->id,
                    'ei_id' => $ei,
                ]))->save();
            }
        }

        return true;
    }

    /**
     * Регистрация заказчика.
     * This method is used to register new user account. If Module::enableConfirmation is set true, this method
     * will generate new confirmation token and use mailer to send it to the user.
     * @throws \Exception
     * @return bool
     */
    public function registerCustomer()
    {
        if ($this->getIsNewRecord() == false) {
            throw new \RuntimeException('Calling "' . __CLASS__ . '::' . __METHOD__ . '" on existing user');
        }

        $transaction = $this->getDb()->beginTransaction();

        try {
            $this->confirmed_at = $this->module->enableConfirmation ? null : time();
            $this->password     = $this->module->enableGeneratingPassword ? Password::generate(8) : $this->password;

            $this->trigger(self::BEFORE_REGISTER);

            if (!$this->save()) {
                $transaction->rollBack();
                return false;
            }

            if ($this->module->enableConfirmation) {
                /** @var Token $token */
                $token = \Yii::createObject(['class' => Token::className(), 'type' => Token::TYPE_CONFIRMATION]);
                $token->link('user', $this);
            }

            $this->mailer->sendWelcomeMessageCustomer($this, isset($token) ? $token : null);
            $this->trigger(self::AFTER_REGISTER);

            $transaction->commit();

            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::warning($e->getMessage());
            throw $e;
        }
    }

    /**
     * Формирует пользовательское меню в сайдбаре для пользователей в подсистеме корпоративной почты.
     * @return array
     */
    public static function prepateSidebarMenuForCemail()
    {
        $mailboxo = [
            ['label' => 'Все ящики', 'url' => ['/mailboxes']],
        ];
        $mailboxes = CEMailboxes::find()->orderBy('created_at')->all();
        foreach ($mailboxes as $mailbox) {
            //$messagesCount = $mailbox->messagesCount2;
            $label = $mailbox->name;
            /*
             * было раньше так:
            $messagesCount = 0;
            if ($messagesCount > 0) $label .= '<span class="badge badge-danger" title="' . $messagesCount . ' (если плохо видно)">' . $messagesCount . '</span>';
            */
            $label .= '<small id="mbcb' . $mailbox->id . '" class="badge" title="Вычисляется количество писем..."><i class="fa fa-spinner fa-pulse fa-fw text-warning"></i><span class="sr-only">Подождите...</span></small>';

            $mailboxo[] = ['label' => $label, 'linkClass' => 'nav-link small', 'url' => ['/mail', (new CEMessagesSearch())->formName() => [
                'mailbox_id' => $mailbox->id,
            ]]];
        }

        $items = [
            ['label' => '<li class="nav-title"><i class="fa fa-envelope"></i> &nbsp;Корпоративная почта</li>'],
            ['label' => 'Все письма', 'icon' => 'fa fa-envelope-o', 'url' => ['/mail']],
            ['label' => 'Все вложения', 'icon' => 'fa fa-paperclip', 'url' => ['/attached-files']],
            ['label' => 'Почтовые ящики', 'icon' => 'fa fa-at', 'url' => '#', 'items' => $mailboxo],
        ];

        $items[] = ['label' => '<li class="nav-title"><i class="fa fa-cog"></i> &nbsp;Управление</li>'];
        $items[] = ['label' => 'Категории', 'icon' => 'fa fa-folder', 'url' => ['/categories']];
        $items[] = ['label' => 'Доступ', 'icon' => 'fa fa-users', 'url' => ['/users-access']];

        return $items;
    }

    /**
     * Делает выборку пользователей веб-приложения и возвращает в виде массива.
     * Применяется для вывода в виджетах Select2.
     * @param $roleFilter integer признак, определяющий необходимость отбора только по роли
     * @param $exclude array массив идентификаторов пользователей для исключения
     * @return array
     */
    public static function arrayMapForSelect2($roleFilter = self::ARRAY_MAP_OF_USERS_BY_MANAGER_ROLE, $exclude = null)
    {
        $assignTop = true;
        $roleName = 'sales_department_manager';

        // если пользователь не авторизован, то ничего не отдаем
        if (Yii::$app->user->isGuest) { $roleFilter = 'restricted'; }

        switch ($roleFilter) {
            case self::ARRAY_MAP_OF_USERS_BY_ALL_ROLES:
                return ArrayHelper::map(self::find()->where(['blocked_at' => null])->joinWith('profile')->orderBy('profile.name')->all(), 'id', 'profile.name');

            case self::ARRAY_MAP_OF_ALL_EXCEPT_SELF:
                // все пользователи, кроме самого себя
                return ArrayHelper::map(self::find()->select([
                    self::tableName() . '.*',
                    'id' => self::tableName() . '.id',
                    'profileName' => Profile::tableName() . '.name',
                ])->joinWith('profile')->where(self::tableName() . '.id <> ' . Yii::$app->user->id)->orderBy(['profileName' => SORT_ASC])->all(), 'id', 'profileName');

            case self::ARRAY_MAP_OF_ALL_EXCEPT_SPECIAL:
                // все пользователи, кроме переданных в параметрах
                return ArrayHelper::map(self::find()->select([
                    self::tableName() . '.*',
                    'id' => self::tableName() . '.id',
                    'profileName' => Profile::tableName() . '.name',
                ])->joinWith('profile')->where(['not in', self::tableName() . '.id', $exclude])->orderBy(['profileName' => SORT_ASC])->all(), 'id', 'profileName');
                break;
            case self::ARRAY_MAP_OF_USERS_BY_MANAGER_ROLE:
                // default case
                break;
            case self::ARRAY_MAP_OF_USERS_BY_ECOLOGIST_ROLE:
                $roleName = 'ecologist';
                break;
            case self::ARRAY_MAP_OF_USERS_BY_MANAGER_AND_ECOLOGIST_ROLE:
                $roleName = ['sales_department_manager', 'ecologist', 'ecologist_head'];
                break;
            case self::ARRAY_MAP_OF_USERS_BY_LOGIST_ROLE:
                $roleName = ['logist'];
                break;
            case self::USERS_ALL_JOIN_FINANCE_BALANCE:
                // массив всех пользователей с дополнением в виде баланса по подотчету
                $result = self::find()->select([
                    self::tableName() . '.`id`',
                    'username',
                    'name' => Profile::tableName() . '.`name`',
                    'balance' => FinanceAdvanceHolders::tableName() . '.`balance`',
                ])
                    ->leftJoin(Profile::tableName(), Profile::tableName() . '.`user_id` = `user`.`id`')
                    ->leftJoin(FinanceAdvanceHolders::tableName(), FinanceAdvanceHolders::tableName() . '.`user_id` = `user`.`id`')
                    ->orderBy(Profile::tableName() . '.name')->asArray()->all();

                foreach ($result as $index => $user) {
                    $balance = '';

                    // если в профиле задано имя, берем его, а если не задано, то берем имя учетной записи
                    if (!empty($user['name'])) {
                        $name = trim($user['name']);
                    }
                    else {
                        $name = trim($user['username']);
                    }

                    // если пользователю когда-либо выдавались деньги подотчет, то дополним его наименование балансом
                    // даже если он нулевой (чтобы понимать, что он ничего не должен)
                    if ($user['balance'] !== null) {
                        $balance = ' (<span class="text-bold ' . ($user['balance'] < 0 ? 'text-info' : 'text-warning') . '" title="' . ($user['balance'] < 0 ? 'Задложенность перед сотрудником' : 'Задолженность сотрудника') . '">' . FinanceTransactions::getPrettyAmount($user['balance']) . '</span> &#8381;)';
                    }

                    $result[$index]['name'] = $name . $balance;
                }

                return ArrayHelper::map($result, 'id', 'name');
            case self::USERS_ALL_WEB_APP:
                // все пользователи, кто работает в веб-приложении
                return ArrayHelper::map(self::find()->where([
                    'not in', AuthAssignment::tableName() . '.`item_name`', ['ferryman', 'foreignDriver', 'customer'],
                ])->joinWith(['profile', 'userRoles'])->orderBy('profile.name')->all(), 'id', 'profile.name');
            case self::USERS_TENDERS:
                $roleName = ['tenders_manager'];
                $assignTop = false;
                break;
            case self::USERS_FO_ATTACHED:
                // все пользователи, кто имеет привязку к Fresh Office
                return ArrayHelper::map(self::find()->where([
                    'not in', AuthAssignment::tableName() . '.`item_name`', ['ferryman', 'foreignDriver', 'customer'],
                ])->andWhere([
                    'is not', 'profile.fo_id', null
                ])->joinWith(['profile', 'userRoles'])->orderBy('profile.name')->all(), 'id', 'profile.name');
                break;
        }

        $result = ArrayHelper::map(self::find()->select(self::tableName() . '.*')
            ->leftJoin('`auth_assignment`', '`auth_assignment`.`user_id`=' . self::tableName().'.`id`')
            ->leftJoin('`profile`', '`profile`.`user_id` = `user`.`id`')
            ->where(['in', '`auth_assignment`.`item_name`', $roleName])->orderBy('profile.name')->all(), 'id', 'profile.name');

        if ($assignTop) {
            // убираем из списка Текучеву
            if (isset($result[145])) unset($result[145]);

            return ArrayHelper::merge([
                1 => 'Алексей Бугров',
                145 => 'Текучева Елена', // исправить когда-нибудь, сделать, чтобы выбирался пользователь и его роль, а не вот это вот все
            ], $result);
        }
        else {
            return $result;
        }
    }

    /**
     * Делает запрос с целью установления наименования менеджера по имеющемуся идентификатору.
     * @param $id integer идентификатор менеджера
     * @return string
     */
    public static function getFreshOfficeManagerName($id)
    {
        $man = DirectMSSQLQueries::fetchManager($id);
        if (is_array($man)) if (count($man) > 0) return $man[0]['name'];
        return '';
    }

    /**
     * Выполняет проверку, используется ли запись в других элементах.
     * @return bool
     */
    public function checkIfUsed()
    {
        if ($this->getEcoProjects()->count() > 0 || $this->getEcoProjectsAccesses()->count() > 0 || $this->getEcoProjectsMilestonesFiles()->count() > 0) return true;

        return false;
    }

    /**
     * Возвращает значение состояния взаиморасчетов с подотчетным лицом, хранящееся явно.
     * @return float|bool
     */
    public function getAdvanceBalanceStored()
    {
        $result = FinanceAdvanceHolders::find()->select('balance')->where(['user_id' => $this->id])->scalar();
        if (false !== $result) floatval($result);
        return $result;
    }

    /**
     * Рассчитывает и возвращает текущую сумму взаиморасчетов с подотчетным лицом.
     * @return float|bool
     */
    public function getAdvanceBalanceCalculated()
    {
        $result = FinanceTransactions::find()->select(new Expression('SUM(amount)'))->where(['user_id' => $this->id])->groupBy('user_id')->scalar();
        if (false !== $result) floatval($result);
        return $result;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfile()
    {
        return $this->hasOne(Profile::className(), ['user_id' => 'id']);
    }

    /**
     * Возвращает ФИО пользователя.
     * @return string
     */
    public function getProfileName()
    {
        return $this->profile == null ? '' : $this->profile->name;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserRoles()
    {
        return $this->hasMany(AuthAssignment::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasOne(AuthItem::class, ['name' => 'item_name'])->via('userRoles');
    }

    /**
     * Возвращает наименование роли пользователя.
     * @return string
     */
    public function getRoleName()
    {
        return !empty($this->role) ? $this->role->name : '';
    }

    /**
     * Возвращает описание роли пользователя.
     * @return string
     */
    public function getRoleDescription()
    {
        return !empty($this->role) ? $this->role->description : '';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEcoProjects()
    {
        return $this->hasMany(EcoProjects::class, ['created_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEcoProjectsAccesses()
    {
        return $this->hasMany(EcoProjectsAccess::class, ['user_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEcoProjectsMilestonesFiles()
    {
        return $this->hasMany(EcoProjectsMilestonesFiles::class, ['uploaded_by' => 'id']);
    }
}
