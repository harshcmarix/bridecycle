<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Sizes */

$this->title = 'View Size';
$this->params['breadcrumbs'][] = ['label' => 'Sizes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">

        <div class="sizes-view">

            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    //'id',
                    'size',
                    [
                        'attribute' => 'product_category_id',
                        'value' => function ($model) {
                            return (!empty($model) && $model instanceOf \app\models\Sizes && !empty($model->product_category_id) && !empty($model->productCategory) && $model->productCategory instanceof \app\models\ProductCategory && !empty($model->productCategory->name)) ? $model->productCategory->name : "";
                        }
                    ],
//                    [
//                        'attribute' => 'status',
//                        'value' => function ($model) {
//                            return (!empty($model) && $model instanceOf \app\models\Sizes && !empty($model->status) && $model->status == \app\models\Sizes::STATUS_ACTIVE) ? $model->arrStatus[\app\models\Sizes::STATUS_ACTIVE] : $model->arrStatus[\app\models\Sizes::STATUS_INACTIVE];
//                        }
//                    ],
                    'created_at',
                    'updated_at',
                ],
            ]) ?>

            <p>
                <?= Html::a('Back', \yii\helpers\Url::to(['index']), ['class' => 'btn btn-default']) ?>
            </p>

        </div>

    </div>
</div>