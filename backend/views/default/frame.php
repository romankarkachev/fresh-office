<?php

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Добро пожаловать! | '.Yii::$app->name;

$planet = 'http://31.148.13.223:8081/uploads/transport-requests/p4gpiu1-h8cefju4u-ebnttzf8lues6h.pdf';
$planet = 'http://31.148.13.223:8081/uploads/transport-requests/gj4qfue7ldm5ambryk0wdltqbyvypd7l.docx';
//$planet = 'http://31.148.13.223:8081/uploads/transport-requests/wbftjzvhsrrhlbujqai42a2sly9ezary.jpg';
//$planet = 'http://31.148.13.223:8081/uploads/transport-requests/zrjp90zgyg8s_w_fcj9c9v0mitsan9pg.xlsx';
?>
<div class="frame">
    <p><?= Html::a('Показать контент', '#', ['class' => 'btn btn-success', 'id' => 'btnShowHiddenContent']) ?></p>
    <div class="form-group">
        <iframe id="content" width="100%" height="800" alt="Попробуйте в другом браузере" frameborder="0"></iframe>
    </div>
</div>
<?php
$this->registerJs(<<<JS
function btnShowHiddenContentOnClick() {
    $("#content").attr("src", "http://docs.google.com/viewer?url=$planet&embedded=true&toolbar=false");

    return false;
} // btnShowHiddenContentOnClick()

$(document).on("click", "#btnShowHiddenContent", btnShowHiddenContentOnClick);
JS
, yii\web\View::POS_READY);
?>
