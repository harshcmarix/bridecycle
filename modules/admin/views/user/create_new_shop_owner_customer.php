<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\User */

$this->title = 'Create Shop Owner';
$this->params['breadcrumbs'][] = ['label' => 'New Shop Owner', 'url' => ['index-new-shop-owner-customer']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="users-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
