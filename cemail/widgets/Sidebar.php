<?php

namespace cemail\widgets;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use romankarkachev\coreui\widgets\Sidebar as BaseSidebar;

/**
 * Class Sidebar
 * Theme sidebar widget.
 */
class Sidebar extends BaseSidebar
{
    public $linkClass = 'nav-link';
    public $linkTemplate = '<a class="{linkClass}" href="{url}">{icon} {label}</a>';

    /**
     * @inheritdoc
     */
    protected function renderItem($item)
    {
        if(isset($item['items'])) {
            $labelTemplate = '
                <li class="nav-item nav-dropdown">
                    <a class="nav-link nav-dropdown-toggle" href="{url}">{label}</a>';
            $linkTemplate = '
                <li class="nav-item nav-dropdown">
                    <a class="nav-link nav-dropdown-toggle" href="{url}">{icon} {label}</a>';
        }
        else {
            $labelTemplate = $this->labelTemplate;
            $linkTemplate = $this->linkTemplate;
        }

        if (isset($item['url'])) {
            $template = ArrayHelper::getValue($item, 'template', $linkTemplate);
            $replace = !empty($item['icon']) ? [
                '{linkClass}' => (!empty($item['linkClass']) ? $item['linkClass'] : $this->linkClass),
                '{url}' => Url::to($item['url']),
                '{label}' => $item['label'],
                '{icon}' => '<i class="' . $item['icon'] .(empty($item['class']) ? '' : ' '.$item['class']).'" aria-hidden="true"></i>'
            ] : [
                '{linkClass}' => (!empty($item['linkClass']) ? $item['linkClass'] : $this->linkClass),
                '{url}' => Url::to($item['url']),
                '{label}' => $item['label'],
                '{icon}' => null,
            ];
            return strtr($template, $replace);
        } else {
            $template = ArrayHelper::getValue($item, 'template', $labelTemplate);
            $replace = !empty($item['icon']) ? [
                '{label}' => $item['label'],
                '{icon}' => '<i class="' . $item['icon'] . '" aria-hidden="true"></i>'
            ] : [
                '{label}' => '<span class="nav-label">'.$item['label'].'</span>',
            ];
            return strtr($template, $replace);
        }
    }
}
