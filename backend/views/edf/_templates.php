<?php

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>
<?= backend\components\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'id' => 'gwTemplates',
    'layout' => '{items}',
    'tableOptions' => ['class' => 'table table-striped table-hover'],
    'columns' => [
        [
            'class' => 'yii\grid\CheckboxColumn',
            'options' => ['width' => 90],
        ],
        'name',
    ],
]); ?>

<?php
$this->registerJs(<<<JS
var allTemplatesChecked = true;

$("input").iCheck({checkboxClass: "icheckbox_square-green"});

// Обработчик щелчка по ссылке "Отметить все документы".
//
function checkAllDocumentsOnClick() {
    if (allTemplatesChecked) {
        operation = "uncheck";
        allTemplatesChecked = false;
    }
    else {
        operation = "check";
        allTemplatesChecked = true;
    }

    $("input[name ^= 'selection[]']").iCheck(operation);

    return false;
} // checkAllDocumentsOnClick()

$("input[name ^= 'selection[]'], input[name = selection_all]").iCheck("check"); // отмечаем все документы
$("input[name = selection_all]").on("ifChanged", checkAllDocumentsOnClick);
JS
, yii\web\View::POS_READY);
?>
