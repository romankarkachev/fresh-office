<?php
namespace backend\controllers;

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
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error', 'request-preview'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'frame'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
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
        return $this->render('index');
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionRequestPreview()
    {
        $document = 'http://docs.google.com/viewer?url=http://31.148.13.223:8081/uploads/transport-requests/gj4qfue7ldm5ambryk0wdltqbyvypd7l.docx';

        $client = new \yii\httpclient\Client();
        $response = $client->createRequest()
            ->setMethod('get')
            ->setUrl($document)
            ->send();
        if ($response->isOk) {
            $content = $response->content;
            $content = str_replace('', '', $content);
            var_dump();
        }
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionFrame()
    {
        return $this->render('frame');
    }
}
