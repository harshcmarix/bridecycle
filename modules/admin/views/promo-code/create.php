<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\PromoCode */

$this->title = 'Create Promo Code';
$this->params['breadcrumbs'][] = ['label' => 'Promo Codes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="promo-code-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
