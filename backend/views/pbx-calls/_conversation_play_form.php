<?php

/* @var $this yii\web\View */
?>
<div class="conversation-play-form">
    <?= xj\jplayer\AudioWidget::widget([
        'id' => 'calls',
        'jsOptions' => [
            'supplied' => "mp3,wav",
            'wmode' => "window",
            'smoothPlayBar' => true,
            'keyEnabled' => true,
            'remainingDuration' => true,
            'toggleDuration' => true
        ],
    ]); ?>

</div>
