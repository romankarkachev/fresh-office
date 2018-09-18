<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $mailboxes array */
?>

<ul class="nav">
<?php foreach ($mailboxes as $mailbox): ?>
                    <li class="nav-item">
                        <?= Html::a('<i class="fa fa-inbox"></i> ' . $mailbox->name . '<span class="badge badge-danger">4</span>', ['/'], ['class' => 'nav-link']) ?>

                    </li>
<?php endforeach; ?>
                </ul>
