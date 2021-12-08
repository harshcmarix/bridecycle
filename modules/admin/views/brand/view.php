<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\bootstrap\Modal;
use app\models\Brand;

/* @var $this yii\web\View */
/* @var $model app\models\Brand */

$this->title = 'View Brand';
$this->params['breadcrumbs'][] = ['label' => 'Brands', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">
        <div class="brand-view">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    [
                        'attribute' => 'name',
                        'value' => function ($model) {
                            $name = '';
                            if ($model instanceof Brand) {
                                $name = $model->name;
                            }
                            return $name;
                        },
                        'header' => '',
                        'headerOptions' => ['class' => 'kartik-sheet-style']
                    ],
                    [
                        'format' => ['raw'],
                        'attribute' => 'image',
                        'value' => function ($model) {
                            $image_path = "";
                            if (!empty($model->image) && file_exists(Yii::getAlias('@brandImageRelativePath') . '/' . $model->image)) {
                                $image_path = Yii::getAlias('@brandImageAbsolutePath') . '/' . $model->image;
                            } else {
                                $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                            }
                            Modal::begin([
                                'id' => 'brandmodal_' . $model->id,
                                'header' => '<h3>Brand Image</h3>',
                                'size' => Modal::SIZE_DEFAULT
                            ]);

                            echo Html::img($image_path, ['width' => '570']);

                            Modal::end();
                            $brandmodal = "brandmodal('" . $model->id . "');";
                            return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $brandmodal, 'height' => '100px', 'width' => '100px']);
                        },
                        'header' => '',
                        'headerOptions' => ['class' => 'kartik-sheet-style']
                    ],
                    [
                        'attribute' => 'status',
                        'value' => function ($model) {
                            $status = "";
                            if ($model->status == Brand::STATUS_PENDING_APPROVAL) {
                                $status = "Pending Approval";
                            } elseif ($model->status == Brand::STATUS_APPROVE) {
                                $status = "Approved";
                            } elseif ($model->status == Brand::STATUS_DECLINE) {
                                $status = "Decline";
                            }
                            return $status;
                        },
                        'header' => '',
                        'headerOptions' => ['class' => 'kartik-sheet-style']
                    ],
                ],
            ]) ?>
            <p>
                <?= Html::a('Back', Url::to(['index']), ['class' => 'btn btn-default']) ?>
            </p>

        </div>

    </div>
</div>

<script type="text/javascript">
    function brandmodal(id) {
        $('#brandmodal_' + id).modal('show');
    }
</script>