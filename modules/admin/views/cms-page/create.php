<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\CmsPage */

$this->title = 'Create Cms Page';
$this->params['breadcrumbs'][] = ['label' => 'Cms Pages', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="cms-page-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
