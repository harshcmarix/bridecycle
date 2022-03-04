<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\BlockUser */

$this->title = 'Create Block User';
$this->params['breadcrumbs'][] = ['label' => 'Block Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="block-user-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
