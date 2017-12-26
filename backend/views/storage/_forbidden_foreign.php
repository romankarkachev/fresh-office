<?php

/* @var $this yii\web\View */
/* @var $details array */
?>
<div class="record-forbidden_foreign">
    <div class="alert alert-danger" role="alert">
        <h4><i class="fa fa-bolt"></i> Невозможно прочитать файлы компании &laquo<?= $details['modelRep'] ?>&raquo;!</h4>
        <p>Файлы контрагента не могут быть отображены, поскольку Вы не являетесь у него ответственным.</p>
    </div>
</div>
