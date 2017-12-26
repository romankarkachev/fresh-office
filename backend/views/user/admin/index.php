<?php

use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;
use dektrium\user\models\UserSearch;

/* @var View $this */
/* @var ActiveDataProvider $dataProvider */
/* @var UserSearch $searchModel */
/* @var $searchApplied bool */

$this->title = Yii::t('user', 'Manage users').' | '.Yii::$app->name;
$this->params['breadcrumbs'][] = 'Пользователи';

$this->params['content-block'] = 'Пользователи';
$this->params['content-additional'] = 'Создание, удаление пользователей, а также изменение информации о них, установка паролей, блокировка.';
?>

<?= $this->render('/_alert', [
    'module' => Yii::$app->getModule('user'),
]) ?>

<?= $this->render('_search', ['model' => $searchModel, 'searchApplied' => $searchApplied]); ?>

<p>
    <?= Html::a('<i class="fa fa-plus-circle"></i> Создать', ['/users/create'], ['class' => 'btn btn-success']) ?>

    <?= Html::a('<i class="fa fa-filter"></i> Отбор', ['#frm-search'], ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default'), 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'frm-search']) ?>

</p>
<?php Pjax::begin() ?>

<?= GridView::widget([
    'dataProvider' 	=> $dataProvider,
    'layout' => '{items}{pager}',
    'tableOptions' => ['class' => 'table table-striped table-hover'],
    'columns' => [
        [
            'attribute' => 'username',
            'format' => 'raw',
            'value' => function($model, $key, $index, $column) {
                /* @var $model \common\models\User */
                /* @var $column \yii\grid\DataColumn */

                $icon = '';
                if ($model->fo_id != null) $icon = ' ' . Html::img(Url::to(['/images/freshoffice16.png']), ['title' => 'Пользователь привязан к учетной записи во Fresh Office']);

                return $model->{$column->attribute} . $icon;
            },
        ],
        'email:email',
        'profileName',
        'roleName',
        [
            'header' => Yii::t('user', 'Block status'),
            'value' => function($model, $key, $index, $column) {
                /* @var $model \common\models\User */
                /* @var $column \yii\grid\DataColumn */

                if ($model->isBlocked) {
                    return Html::a(Yii::t('user', 'Unblock'), ['block', 'id' => $model->id], [
                        'class' => 'btn btn-xs btn-success btn-block',
                        'data-method' => 'post',
                        'data-confirm' => Yii::t('user', 'Are you sure you want to unblock this user?'),
                    ]);
                } else {
                    return Html::a(Yii::t('user', 'Block'), ['block', 'id' => $model->id], [
                        'class' => 'btn btn-xs btn-danger btn-block',
                        'data-method' => 'post',
                        'data-confirm' => Yii::t('user', 'Are you sure you want to block this user?'),
                    ]);
                }
            },
            'format' => 'raw',
            'options' => ['width' => 130, 'text-align' => 'center'],
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'header' => 'Действия',
            'template'=>'{update} {delete}',
            'buttons' => [
                'update' => function ($url, $model) {
                    /* @var $model \common\models\User */

                    return Html::a('<i class="fa fa-pencil" aria-hidden="true"></i>', ['/users/update', 'id' => $model->id], ['class' => 'btn btn-default btn-xs', 'title' => 'Редактировать', 'data-pjax' => '0']);
                },
                'delete' => function ($url, $model) {
                    /* @var $model \common\models\User */
                    
                    return Html::a('<i class="fa fa-trash-o" aria-hidden="true"></i>', ['/users/delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger btn-xs',
                        'title' => 'Удалить элемент',
                        'data-confirm' => 'Вы действительно хотите удалить этот элемент?',
                        'data-method' => 'post',
                    ]);
                }
            ],
            'options' => ['width' => 80, 'text-align' => 'center'],
        ],
    ],
]); ?>

<?php Pjax::end() ?>
