<?php

use yii\helpers\HtmlPurifier;
use yii\web\View;
use backend\controllers\CounteragentsCrmController;

/* @var $this yii\web\View */
/* @var $model common\models\foCompany */
/* @var $edfInfo string информация о действующих договорах */

$this->title = $model->COMPANY_NAME . HtmlPurifier::process(' &mdash; ' . CounteragentsCrmController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = CounteragentsCrmController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = $model->COMPANY_NAME;

$contactPersons = $model->contactPersonsAsDataProvider;
$contactPersonsCount = $contactPersons->getTotalCount();

$tasks = $model->tasksAsDataProvider;
$tasksCrm = $model->tasksCrmAsDataProvider;
$tasksCount = $tasks->getTotalCount() + $tasksCrm->getTotalCount();

$ecoProjects = $model->ecoProjectsAsDataProvider;
$ecoProjectsCount = $ecoProjects->getTotalCount();

$ecoContracts = $model->ecoContractsAsDataProvider;
$ecoContractsCount = $ecoContracts->getTotalCount();

$calls = $model->callsAsDataProvider;
$callsCount = $calls->getTotalCount();

$projects = $model->projectsAsDataProvider;
$projectsCount = $projects->getTotalCount();

$im = $model->incomingMailAsDataProvider;
$imCount = $im->getTotalCount();

$edf = $model->edfAsDataProvider;
$edfCount = $edf->getTotalCount();
?>
<div class="fo-company-update">
    <ul class="nav nav-tabs" role="tablist" style="margin-bottom: 15px;">
        <li role="presentation" class="active"><a href="#common" aria-controls="common" role="tab" data-toggle="tab">Общие</a></li>
        <?php if ($contactPersonsCount > 0): ?>
        <li role="presentation"><a href="#contact-persons" aria-controls="contact-persons" role="tab" data-toggle="tab">Контактные лица<?= empty($contactPersonsCount) ? '' : ' (' . $contactPersonsCount . ')' ?></a></li>
        <?php endif; ?>
        <?php if ($tasksCount > 0): ?>
        <li role="presentation"><a href="#tasks" aria-controls="tasks" role="tab" data-toggle="tab">Задачи<?= empty($tasksCount) ? '' : ' (' . $tasksCount . ')' ?></a></li>
        <?php endif; ?>
        <?php if ($ecoProjectsCount > 0): ?>
        <li role="presentation"><a href="#eco-projects" aria-controls="eco-projects" role="tab" data-toggle="tab" title="Проекты по экологии">Проекты <i class="fa fa-leaf text-success"></i><?= empty($ecoProjectsCount) ? '' : ' (' . $ecoProjectsCount . ')' ?></a></li>
        <?php endif; ?>
        <?php if ($ecoContractsCount > 0): ?>
        <li role="presentation"><a href="#eco-contracts" aria-controls="eco-contracts" role="tab" data-toggle="tab" title="Договоры совпровождения по экологии">Договоры <i class="fa fa-leaf fa-lg text-success"></i><?= empty($ecoContractsCount) ? '' : ' (' . $ecoContractsCount . ')' ?></a></li>
        <?php endif; ?>
        <?php if ($callsCount > 0): ?>
        <li role="presentation"><a href="#calls" aria-controls="calls" role="tab" data-toggle="tab">Звонки<?= empty($callsCount) ? '' : ' (' . $callsCount . ')' ?></a></li>
        <?php endif; ?>
        <?php if ($projectsCount > 0): ?>
        <li role="presentation"><a href="#projects" aria-controls="projects" role="tab" data-toggle="tab">Проекты<?= empty($projectsCount) ? '' : ' (' . $projectsCount . ')' ?></a></li>
        <?php endif; ?>
        <?php if ($imCount > 0): ?>
        <li role="presentation"><a href="#im" aria-controls="im" role="tab" data-toggle="tab">Вх. корр.<?= empty($imCount) ? '' : ' (' . $imCount . ')' ?></a></li>
        <?php endif; ?>
        <?php if ($edfCount > 0): ?>
        <li role="presentation"><a href="#edf" aria-controls="edf" role="tab" data-toggle="tab">Документооборот<?= empty($edfCount) ? '' : ' (' . $edfCount . ')' ?></a></li>
        <?php endif; ?>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="common">
            <?php if (!empty($edfInfo)): ?>
            <div class="form-group">
                <label class="control-label">Действующие договоры</label>
                <p><?= $edfInfo; ?></p>
            </div>
            <?php endif; ?>
            <?= $this->render('_form', ['model' => $model]) ?>

        </div>
        <?php if ($contactPersonsCount > 0): ?>
        <div role="tabpanel" class="tab-pane" id="contact-persons">
            <?= $this->render('_contact_persons', ['dataProvider' => $contactPersons]) ?>

        </div>
        <?php endif; ?>
        <?php if ($tasksCount > 0): ?>
        <div role="tabpanel" class="tab-pane" id="tasks">
            <?= $this->render('_tasks', ['dataProvider' => $tasks]) ?>

            <?= $this->render('_tasks_crm', ['dataProvider' => $tasksCrm]) ?>

        </div>
        <?php endif; ?>
        <?php if ($ecoProjectsCount > 0): ?>
        <div role="tabpanel" class="tab-pane" id="eco-projects">
            <?= $this->render('_eco_projects', ['dataProvider' => $ecoProjects]) ?>

        </div>
        <?php endif; ?>
        <?php if ($ecoContractsCount > 0): ?>
        <div role="tabpanel" class="tab-pane" id="eco-contracts">
            <?= $this->render('_eco_contracts', ['dataProvider' => $ecoContracts]) ?>

        </div>
        <?php endif; ?>
        <?php if ($callsCount > 0): ?>
        <div role="tabpanel" class="tab-pane" id="calls">
            <?= $this->render('_calls', ['dataProvider' => $calls]) ?>

        </div>
        <?php endif; ?>
        <?php if ($projectsCount > 0): ?>
        <div role="tabpanel" class="tab-pane" id="projects">
            <?= $this->render('_projects', ['dataProvider' => $projects]) ?>

        </div>
        <?php endif; ?>
        <?php if ($imCount > 0): ?>
        <div role="tabpanel" class="tab-pane" id="im">
            <?= $this->render('_inc_mail', ['dataProvider' => $im]) ?>

        </div>
        <?php endif; ?>
        <?php if ($edfCount > 0): ?>
        <div role="tabpanel" class="tab-pane" id="edf">
            <?= $this->render('_edf', ['dataProvider' => $edf]) ?>

        </div>
        <?php endif; ?>
    </div>
</div>
<?php

$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js', ['depends' => 'yii\web\JqueryAsset', 'position' => View::POS_END]);

$this->registerJs(<<<JS

// Обработчик щелчка по кнопке "Воспроизведение" в отдельной строке таблицы.
//
function btnPlayOnClick() {
    $("#mvPhoneConversation").modal();

    my_jPlayer = $("#jquery_jplayer_calls");
    my_jPlayer.jPlayer("setMedia", {
        mp3: $(this).attr("href")
    });
    my_jPlayer.jPlayer("play");

    return false;
} // btnPlayOnClick()

$(document).on("click", "a[id ^= 'btnPlay']", btnPlayOnClick);
$("#mvPhoneConversation").on('hidden.bs.modal', function () {
    my_jPlayer = $("#jquery_jplayer_calls");
    my_jPlayer.jPlayer("stop");
});

JS
, View::POS_READY);
?>

