<?php

use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
?>
<div class="transport-requests-similar_statements">
    <?php if ($dataProvider->totalCount > 0): ?>
    <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => '{items}{pager}',
            'tableOptions' => ['class' => 'table table-striped'],
            'columns' => [
                'id',
                'created_at:datetime:Создан',
                'createdByName',
                [
                    'header' => 'Детали',
                    'format' => 'raw',
                    'value' => function($model, $key, $index, $column) {
                        /* @var $model \common\models\TransportRequests */

                        $result = '<strong>Отходы:</strong><br />' . nl2br($model->tpWasteLinear) . '<br /><strong>Транспорт:</strong><p>' . $model->tpTransportLinear . '</p>';
                        $result .= \yii\helpers\Html::a('Открыть запрос &rarr;', [
                            '/transport-requests/update',
                            'id' => $model->id
                        ], [
                            'target' => '_blank',
                            'title' => 'Открыть запрос в новом окне',
                        ]);
                        return $result;
                    },
                ],
            ],
    ]); ?>

    <?php else: ?>
    <p class="text-muted">Закрытых запросов по данному контрагенту не обнаружено.</p>
    <?php endif; ?>
</div>
