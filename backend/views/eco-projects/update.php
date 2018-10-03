<?php

use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use backend\controllers\EcoProjectsController;

/* @var $this yii\web\View */
/* @var $model common\models\EcoProjects */
/* @var $newAccessModel \common\models\EcoProjectsAccess */
/* @var $dpAccess \yii\data\ActiveDataProvider */
/* @var $dpMilestones \yii\data\ActiveDataProvider */

$dateStart = Yii::$app->formatter->asDate($model->date_start . ' 00:00:00', 'php:d.m.Y');
$dateFinish = Yii::$app->formatter->asDate($model->date_close_plan . ' 00:00:00', 'php:d.m.Y');
$this->title = 'Проект № ' . $model->id . ' (работы с ' . $dateStart . ' по ' . $dateFinish . ')' . HtmlPurifier::process(' &mdash; ' . EcoProjectsController::ROOT_LABEL . ' | ') . Yii::$app->name;
$this->params['breadcrumbs'][] = EcoProjectsController::ROOT_BREADCRUMB;
$this->params['breadcrumbs'][] = 'Проект № ' . $model->id . ' (' . $dateStart . ' &mdash; ' . $dateFinish . ')';

$iconSuccess = '<i class=\"fa fa-check-circle text-success\" aria-hidden=\"true\" title=\"Проект завершен\"></i>';
?>
<div class="eco-projects-update">
    <?php $form = ActiveForm::begin(); ?>

    <?= $this->render('_project_details', ['model' => $model]) ?>

    <?= $form->field($model, 'comment')->textarea(['rows' => 3, 'placeholder' => 'Введите произвольный комментарий']) ?>

    <div class="form-group">
        <?= Html::a('<i class="fa fa-arrow-left" aria-hidden="true"></i> ' . EcoProjectsController::ROOT_LABEL, EcoProjectsController::ROOT_URL_AS_ARRAY, ['class' => 'btn btn-default btn-lg', 'title' => 'Вернуться в список. Изменения не будут сохранены']) ?>

        <?= Html::submitButton('<i class="fa fa-floppy-o" aria-hidden="true"></i> Сохранить', ['class' => 'btn btn-primary btn-lg']) ?>

    </div>
    <?php ActiveForm::end(); ?>

    <?= $this->render('milestones_list', [
        'model' => $model,
        'dataProvider' => $dpMilestones,
    ]); ?>

    <?php if (Yii::$app->user->can('root') || Yii::$app->user->can('ecologist_head')): ?>
    <?= $this->render('access_list', [
        'dataProvider' => $dpAccess,
        'model' => $newAccessModel,
        'action' => EcoProjectsController::URL_ADD_USER_ACCESS,
    ]); ?>

    <?php endif; ?>
</div>
<?php
$urlCloseDate = Url::to(['/eco-projects/change-close-date']);
$urlCloseMilestone = Url::to(['/eco-projects/close-milestone']);
$urlRenderCloseDateBlock = Url::to(['/eco-projects/render-close-date-block']);

$this->registerJs(<<<JS
// Функция выполняет загрузку через ajax блока с планируемыми этапами работы.
//
function dateClosePlanOnChange(e, id, new_date) {
    \$block = $("#blockTermin" + id);
    \$html = \$block.html();
    $.post("$urlCloseDate?id=" + id + "&new_date=" + new_date).success(function(response) {
        if (response.result == true) {
            \$block.replaceWith(response.terminColumnHtml);
        }
        else {
            \$block.html(\$html);
        }
    });
} // dateClosePlanOnChange()

JS
, \yii\web\View::POS_BEGIN);

$this->registerJs(<<<JS
// Обработчик щелчка по кнопкам "Завершить этап".
//
function closeMilestoneOnClick() {
    \$btn = $(this);
    id = \$btn.attr("data-id");
    if (!$.isEmptyObject(id) && confirm("Этап будет закрыт. Если это последний этап, то будет закрыт и проект. Операция необратима. Продолжить?")) {
        html = \$btn.html();
        \$btn.html('<i class="fa fa-spinner fa-pulse fa-fw"></i> <span>Подождите...</span>');
        \$btn.attr("disabled", "disabled");

        // текущая строка таблицы
        \$tr = $("tr[data-key='" + id + "']");

        // следующая строка таблицы
        \$trNext = \$tr.next();

        nextId = "";
        // дополнительный параметр URL - id следующего этапа
        if (\$trNext.length > 0) {
            next_id = \$trNext.attr("data-key");
            nextId = "&next_id=" + next_id;
            \$nextBlockTool = $("#blockTool" + next_id);
        }

        $.post("$urlCloseMilestone?id=" + id + nextId).success(function(response) {
            if (response.result == true) {
                // этап успешно закрыт
                // в графе с плановой датой завершения этапа выводим новый текст в соответствии с правилами
                $("#blockTermin" + id).replaceWith(response.terminColumnHtml);
                // в графе с файлами этапа выводим новое значениев соответствии с правилами
                $("#blockFiles" + id).replaceWith(response.filesColumnHtml);
                // в графе с плановым количеством дней на выполнение этапа выводим новый текст с фактическим количеством, если отличается
                $("#blockRequired" + id).replaceWith(response.requiredColumnHtml);
                // строку делаем обычным шрифтом и обычным цветом
                \$tr.removeClass();
                // а следующую строку раскрашиваем как текущий теперь уже этап
                // но конечно если она существует
                if (\$trNext.length > 0) {
                    \$trNext.addClass("text-bold info");
                    \$nextBlockTool.replaceWith(response.toolNextColumnHtml);
                }
                // кнопку завершения этапа заменяем на зеленую галочку
                $("#blockTool" + id).replaceWith("$iconSuccess");

                if (response.isProjectClosed == true) alert("Последний этап был завершен. Весь проект также завершен.");
            }
            else {
                \$btn.html(html);
                \$btn.removeAttr("disabled");
                alert("Ошибка при завершении проекта!\\r\\n" + response.errorMsg);
            }
        }).fail(function() {
            \$btn.html(html);
            \$btn.removeAttr("disabled");
        });

        return false;
    }
} // closeMilestoneOnClick

// Обработчик щелчка по ссылкам, позволяющим изменить дату завершения этапа.
//
function changeMilestoneCloseDateOnClick() {
    id = $(this).attr("data-id");
    if (!$.isEmptyObject(id) && confirm("Будет открыта возможность изменить сроки по этому этапу, в соответствии с чем будут пересчитаны следующие за ним этапы. Несохраненные изменения в форме будут утрачены. Продолжить?")) {
        \$blockTermin = $("#blockTermin" + id);
        \$blockTermin.html('<i class="fa fa-spinner fa-pulse fa-fw"></i> <small class="text-muted">Подождите...</small>');
        \$blockTermin.load("$urlRenderCloseDateBlock?id=" + id);
    }

    return false;
} // changeMilestoneCloseDateOnClick()

$(document).on("click", "button[id^='closeMilestone']", closeMilestoneOnClick);
$(document).on("click", "a[id^='changeMilestoneCloseDate']", changeMilestoneCloseDateOnClick);
JS
, \yii\web\View::POS_READY);
?>
