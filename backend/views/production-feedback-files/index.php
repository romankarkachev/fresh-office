<?php

use yii\helpers\Html;
use yii\widgets\ListView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ProductionFeedbackFilesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchApplied bool */
/* @var $queryString string */

$this->title = 'Обратная связь от производства | '.Yii::$app->name;
$this->params['breadcrumbs'][] = 'Обратная связь от производства';
?>
<div class="production-feedback-files-list">
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('<i class="fa fa-filter"></i> Отбор', ['#frm-search'], ['class' => 'btn btn-'.($searchApplied ? 'info' : 'default'), 'data-toggle' => 'collapse', 'aria-expanded' => 'false', 'aria-controls' => 'frm-search']) ?>

        <?php if (Yii::$app->user->can('root')): ?>
        <?= Html::a('<i class="fa fa-trash-o" aria-hidden="true"></i> Удалить файлы проекта', '/production-feedback-files/delete-project-files?' . $queryString, [
            'title' => Yii::t('yii', 'Удалить'),
            'class' => 'btn btn-danger pull-right',
            'style' => 'margin-left: 5px;',
            'aria-label' => Yii::t('yii', 'Delete'),
            'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
            'data-method' => 'post',
            'data-pjax' => '0',
        ]); ?>

        <?php endif; ?>
        <?= Html::a('<i class="fa fa-cloud-download" aria-hidden="true"></i> Скачать архивом', '/production-feedback-files?downloadArchive=true' . $queryString, ['class' => 'btn btn-default pull-right']) ?>

    </p>
    <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}<div class="clearfix"></div>{pager}',
        // можно всегда выводить пагинатор
        // 'pager' => ['hideOnSinglePage' => false],
        'itemView' => function ($model, $key, $index, $widget) {
            /* @var $model \common\models\ProductionFeedbackFiles */
            static $prevProjectId = null;
            $result = $this->render('_item', ['item' => $model, 'uploadDir' => '/../../uploads/production-files/', 'renderHeader' => $prevProjectId != $model->project_id]);
            $prevProjectId = $model->project_id;
            return $result;
        },
    ]); ?>

    <?= \newerton\fancybox\FancyBox::widget([
        'target' => 'a[rel^="fancybox"]',
        'helpers' => true,
        'mouse' => true,
        'config' => [
            'maxWidth' => '90%',
            'maxHeight' => '90%',
            'playSpeed' => 4000,
            'padding' => 0,
            'fitToView' => false,
            'width' => '70%',
            'height' => '70%',
            'autoSize' => false,
            'closeClick' => false,
            'openEffect' => 'elastic',
            'closeEffect' => 'elastic',
            'prevEffect' => 'elastic',
            'nextEffect' => 'elastic',
            'closeBtn' => false,
            'openOpacity' => true,
            'helpers' => [
                'title' => ['type' => 'float'],
                'buttons' => [],
                'thumbs' => ['width' => 68, 'height' => 50],
                'overlay' => [
                    'css' => [
                        'background' => 'rgba(0, 0, 0, 0.8)'
                    ],
                    // ура! гадость наконец исправлена!
                    // чтобы не прокручивалось вверх при щелчке по изображению, делаем так:
                    'locked' => false,
                ]
            ],
        ]
    ]); ?>

</div>
