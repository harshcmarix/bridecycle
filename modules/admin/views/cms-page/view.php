<?php

use yii\helpers\{
    Html,
    Url
};
use yii\widgets\DetailView;
use app\models\CmsPage;

/* @var $this yii\web\View */
/* @var $model app\models\CmsPage */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Cms Pages', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="cms-page-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
           [
                'attribute' => 'id',
                'value' => function ($model) {
                    $id = '';
                    if ($model instanceof CmsPage) {
                        $id = $model->id;
                    }
                    return $id;
                },
            ],           
            [
                'attribute' => 'title',
                'value' => function ($model) {
                    $title = '';
                    if ($model instanceof CmsPage) {
                        $title = $model->title;
                    }
                    return $title;
                },
            ],
            // 'slug',
             [
                'attribute' => 'description',
                'value' => function ($model) {
                    $description = '';
                    if ($model instanceof CmsPage) {
                        $description = $model->description;
                    }
                    return $description;
                },
                'format' => ['raw'],
                'filter'=>false,
            ],
             [
                'attribute' => 'created_at',
                'value' => function ($model) {
                    $created_at = '';
                    if ($model instanceof CmsPage) {
                        $created_at = $model->created_at;
                    }
                    return $created_at;
                },
            ],
             [
                'attribute' => 'updated_at',
                'value' => function ($model) {
                    $updated_at = '';
                    if ($model instanceof CmsPage) {
                        $updated_at = $model->updated_at;
                    }
                    return $updated_at;
                },
            ],
        ],
    ]) ?>

    <p>
        <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
    </p>

</div>
