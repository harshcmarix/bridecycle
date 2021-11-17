<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\User */


$this->title = 'Update Shop Owner';
$this->params['breadcrumbs'][] = ['label' => 'Shop Owners', 'url' => ['index']];
// $this->params['breadcrumbs'][] = ['label' => $model->first_name . " " . $model->last_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update Shop Owner';
?>
<div class="users-update">

    <?= $this->render('_form_new_shop_owner_customer', [
        'model' => $model,
    ]) ?>

</div>
