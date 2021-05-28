<?php

namespace app\modules\admin\widgets;

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use \kartik\grid\GridView as kartikGridview;

/**
 * Class GridView
 * @package app\modules\admin\widgets
 */
class GridView extends kartikGridview
{
    public $panelTemplate = '{panelHeading}{panelBefore}{items}{panelAfter}{panelFooter}</div>';
    public $panelHeadingTemplate = '{title}<div class="clearfix"></div>';
    public $panelFooterTemplate = '<div class="row">{summary}<div class="kv-panel-pager col-sm-12 col-md-7">{pager}</div></div>{footer}<div class="clearfix">';
}