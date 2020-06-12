<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\Pjax;
use backend\controllers\UsersController;

/* @var $this yii\web\View */
/* @var $user dektrium\user\models\User */
/* @var $model \common\models\UsersTrusted */
/* @var $dataProvider \yii\data\ArrayDataProvider */

$userProfileName = $model->userProfileName;
?>

<?php Pjax::begin(['id' => 'pjax-trusted', 'timeout' => 5000, 'enableReplaceState' => false, 'enablePushState' => false]); ?>

<?= $this->render('_form', ['model' => $model, 'userProfileName' => $userProfileName]); ?>

<p><?= $userProfileName ?> доверяет доступ следующим пользователям.</p>
<?= \backend\components\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'layout' => '{items}',
    'columns' => [
        [
            'attribute' => 'sectionName',
            'options' => ['width' => 200],
        ],
        'trustedProfileName',
        [
            'class' => 'backend\components\grid\ActionColumn',
            'template' => '{delete}',
            'buttons' => [
                'delete' => function ($url, $model) {
                    $url = ArrayHelper::merge(UsersController::URL_DELETE_TRUSTED_OTF_AS_ARRAY, ['id' => $model->id]);
                    // только так не скроллится наверх (то есть при помощи заключения в форму):
                    return Html::beginForm($url, 'post', ['data-pjax' => true]) .
                        Html::a(
                            '<i class="fa fa-times"></i>',
                            $url,
                            [
                                'title' => Yii::t('yii', 'Удалить'),
                                'class' => 'btn btn-xs btn-danger',
                                'aria-label' => Yii::t('yii', 'Delete'),
                                'data-confirm' => 'Будет выполнено удаление пользователя из доверенных лиц по этому разделу. Продолжить?',
                                'data-method' => 'post',
                                'data-pjax' => '0',
                            ]
                        ) . Html::endForm();
                }
            ],
            'options' => ['width' => '20'],
        ],
    ],
]); ?>

<?php Pjax::end(); ?>
