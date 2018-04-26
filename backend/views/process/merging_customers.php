<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var integer $field поле для поиска (наименование, номер телефона, E-mail) */
/* @var string $criteria значение для отбора */
/* @var $dataProvider \yii\data\ActiveDataProvider таблица одинаковых карточек */

$this->title = 'Объединение карточек контрагентов во Fresh Office | ' . Yii::$app->name;
$this->params['breadcrumbs'][] = 'Объединение контрагентов';

$confirmDeletePrompt = 'Контрагент будет удален из данного списка и не будет участвовать в объединении карточек. Продолжить?';
$fieldName = \common\models\ReportCaDuplicates::getSearchFieldName($field);
?>
<div class="merge-customers">
    <?php $form = ActiveForm::begin(); ?>

    <p>Форма предназначена для объединения карточек контрагентов в CRM &laquo;Fresh Office&raquo;.</p>
    <p>
        Поиск дубликатов осуществяется по полю &laquo;<strong><?= $fieldName ?></strong>&raquo;,
        условие отбора: <strong><?= !empty($criteria) ? $criteria : '<пустая строка>' ?></strong>.
        Всего обнаружено карточек: <strong><?= $dataProvider->totalCount ?></strong>.
        Отметьте одного контрагента в первой графе и нажмите кнопку &laquo;Выполнить&raquo;.
        Если Вы не хотите, чтобы некоторые карточки участвовали в слиянии, удалите их из списка.
        Обратите внимание, что удаление из списка &mdash; это полностью безопасная процедура, карточка не будет удалена
        из CRM.
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'layout' => '{items}{pager}',
        'tableOptions' => ['class' => 'table table-striped table-hover'],
        'columns' => [
            [
                'class' => 'yii\grid\RadioButtonColumn',
                'header' => 'Главный',
                'radioOptions' => function ($model) {
                    return ['value' => $model['id'], 'required' => true];
                },
                'options' => ['width' => '80'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'id',
                'label' => 'ID',
                'format' => 'raw',
                'value' => function ($model, $key, $index, $column) {
                    /* @var $model \common\models\ReportCaDuplicates */
                    /* @var $column \yii\grid\DataColumn */

                    return $model['id'] . Html::hiddenInput('MergeCustomers[]', $model['id']);
                },
                'options' => ['width' => '80'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
            [
                'attribute' => 'name',
                'label' => 'Наименование',
            ],
            [
                'attribute' => 'managerName',
                'label' => 'Менеджер',
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'header' => 'Действия',
                'template' => '{delete}',
                'buttons' => [
                    'delete' => function ($url, $model) {
                        return Html::a('<i class="fa fa-times"></i>', '#', [
                            'id' => 'btnDelete' . $model['id'],
                            'data-id' => $model['id'],
                            'class' => 'btn btn-xs btn-danger',
                            'title' => Yii::t('yii', 'Удалить'),
                            'aria-label' => Yii::t('yii', 'Delete'),
                        ]);
                    }
                ],
                'options' => ['width' => '80'],
                'headerOptions' => ['class' => 'text-center'],
                'contentOptions' => ['class' => 'text-center'],
            ],
        ],
    ]); ?>

    <p>Слияние карточек осуществляется в части следующих данных:</p>
    <ul>
        <li>
            контактные лица
        </li>
        <li>
            контактная информация
        </li>
        <li>
            реквизиты
        </li>
        <li>
            кураторы
        </li>
        <li>
            задачи
        </li>
        <li>
            проекты
        </li>
        <li>
            сделки
        </li>
        <li>
            финансы
        </li>
        <li>
            документы
        </li>
    </ul>
    <?= Html::submitButton('<i class="fa fa-cog"></i> Выполнить', ['class' => 'btn btn-success btn-lg']) ?>

    <?php ActiveForm::end(); ?>

</div>
<?php
$this->registerJs(<<<JS
// Обработчик щелчка по кнопке "Удалить" в списке контрагентов.
//
function btnDeleteOnClick() {
    if (confirm("$confirmDeletePrompt")) {
        id = $(this).attr("data-id");
        if (id != "" && id != null) $("tr[data-key='" + id + "']").remove();
    }

    return false;
} // btnDeleteOnClick()

$(document).on("click", "a[id ^= 'btnDelete']", btnDeleteOnClick);
JS
, \yii\web\View::POS_READY);
?>
