<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Ads */

$this->title = 'Create Ads';
$this->params['breadcrumbs'][] = ['label' => 'Ads', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ads-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
