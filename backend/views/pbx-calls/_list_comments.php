<?php

use yii\widgets\Pjax;
use backend\components\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $model \common\models\pbxCallsComments */
?>
<div class="pbx-calls-comments">
    <?php Pjax::begin(['id' => 'pjax-gw', 'enablePushState' => false]); ?>

    <?= GridView::widget([
            'id' => 'gwComments',
            'dataProvider' => $dataProvider,
            'showHeader'=> false,
            'layout' => "<div style=\"position: relative; min-height: 20px;\"><small class=\"pull-right form-text text-muted\" style=\"position: absolute; bottom: 0; right: 0;\">{summary}</small></div>\n{items}\n{pager}",
            'columns' => [
                [
                    'attribute' => 'added_timestamp',
                    'label' => 'Дата',
                    'format' => ['datetime', 'dd.MM.YYYY HH:mm'],
                    'headerOptions' => ['class' => 'text-center'],
                    'contentOptions' => ['class' => 'text-center'],
                    'options' => ['width' => '130'],
                ],
                [
                    'attribute' => 'createdByProfileName',
                    'options' => ['width' => '150'],
                ],
                [
                    'attribute' => 'contents',
                    'format' => 'raw',
                    'value' => function($model, $key, $index, $column) {
                        /* @var $model \common\models\pbxCallsComments */
                        /* @var $column \yii\grid\DataColumn */

                        return nl2br($model->{$column->attribute});
                    },
                ],
            ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
<?php
$urlReloadCommentsList = yii\helpers\Url::to(['/pbx-calls/render-comments-list', 'id' => $model->call_id]);

$this->registerJs(<<<JS
    $("#pjax-new-comment").on("pjax:success", function(data, status, xhr, options) {
        $.pjax.reload({container: "#gwComments", timeout:5000, url: "$urlReloadCommentsList", replace: false});
    });
JS
, yii\web\View::POS_READY);
?>
