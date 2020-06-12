<?php
namespace backend\controllers;

use common\models\DesktopWidgets;
use common\models\DesktopWidgetsAccess;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * Default controller
 */
class DefaultController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'error', 'phpinfo'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if ($action->id == 'error')
                $this->layout ='na';
            return true;
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $user_id  = Yii::$app->user->id;
        $roles = Yii::$app->authManager->getRolesByUser($user_id);
        if (count($roles) > 0) {
            $role = current($roles)->name;
        }
        $widgets = '';
        foreach (DesktopWidgets::find()->where([
            'id' => DesktopWidgetsAccess::find()->select('widget_id')->where([
                'or',
                ['type' => DesktopWidgetsAccess::TYPE_ROLE, 'entity_id' => $role],
                ['type' => DesktopWidgetsAccess::TYPE_USER, 'entity_id' => $user_id],
            ])
        ])->all() as $widget) {
            if (file_exists(Yii::getAlias('@backend') . '/views/desktop-widgets/widgets/_' . $widget->alias . '.php')) {
                $widgets .= $this->renderPartial('/desktop-widgets/widgets/_' . $widget->alias, ['model' => $widget]);
            }
        }

        return $this->render('index', ['widgets' => $widgets]);
    }

    /**
     * Отображает страницу с информацией о веб-сервере.
     * @return string
     */
    public function actionPhpinfo()
    {
        return $this->render('phpinfo');
    }
}
