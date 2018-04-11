<?php

namespace ferryman\components\grid;

use yii\grid\GridView as BaseGridView;

/**
 * Настройка под проект стандартного GridView.
 * @author Roman Karkachev <post@romankarkachev.ru>
 * @since 2.0
 */
class GridView extends BaseGridView
{
    public $tableOptions = ['class' => 'table table-striped table-responsive'];
    public $layout = "<div style=\"position: relative; min-height: 20px;\"><small class=\"pull-right form-text text-muted\" style=\"position: absolute; bottom: 0; right: 0;\">{summary}</small>\n{pager}</div>\n{items}\n<small class=\"pull-right form-text text-muted\">{summary}</small>\n{pager}";
    public $summary = "Показаны записи с <strong>{begin}</strong> по <strong>{end}</strong>, на странице <strong>{count}</strong>, всего <strong>{totalCount}</strong>. Страница <strong>{page}</strong> из <strong>{pageCount}</strong>.";
    public $pager = [
        'options' => ['class' => 'pagination', 'style' => 'margin-bottom: 5px;'],
        'linkOptions' => ['class' => 'page-link'],
        'pageCssClass' => ['class' => 'page-item'],
        'prevPageCssClass' => ['class' => 'page-item'],
        'disabledListItemSubTagOptions' => ['tag' => 'a', 'class' => 'page-link'],
    ];
}
