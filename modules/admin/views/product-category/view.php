<?php

use yii\helpers\{
    Html,
    Url
};
use yii\widgets\DetailView;
use app\models\ProductCategory;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $model app\models\ProductCategory */

$this->title = 'View Category';
if(!empty($model->parent_category_id)){
    $this->title = 'View Subcategory';
}
$this->params['breadcrumbs'][] = ['label' => 'All Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="box box-default">
    <div class="box-header"></div>
    <div class="box-body">

        <div class="product-category-view">

            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    [
                        'format' => ['raw'],
                        'attribute' => 'image',
                        'value' => function ($model) {
                            $image_path = "";
                            if (!empty($model->image) && file_exists(Yii::getAlias('@productCategoryImageRelativePath') . '/' . $model->image)) {
                                $image_path = Yii::getAlias('@productCategoryImageAbsolutePath') . '/' . $model->image;
                            } else {
                                $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                            }
                            Modal::begin([
                                'id' => 'productcategorymodal_' . $model->id,
                                'header' => '<h3>Category Image</h3>',
                                'size' => Modal::SIZE_DEFAULT
                            ]);

                            echo Html::img($image_path, ['width' => '570']);

                            Modal::end();
                            $productcategorymodal = "productcategorymodal('" . $model->id . "');";
                            return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $productcategorymodal, 'height' => '100px', 'width' => '100px']);
                        },
                        'header' => '',
                        'headerOptions' => ['class' => 'kartik-sheet-style']
                    ],
                    [
                        'attribute' => 'name',
                        'value' => function ($model) {
                            $name = '';
                            if ($model instanceof ProductCategory) {
                                $name = $model->name;
                            }
                            return $name;
                        },
                    ],
                    [
                        'attribute' => 'product_category_id',
                        'label' => 'Parent Category',
                        'value' => function ($model) {
                            $parent_name = '-';
                            if (!empty($model->parent) && $model->parent instanceof ProductCategory) {
                                $parent_name = $model->parent->name;
                            }elseif (empty($model->parent)){
                                $parent_name = $model->name;
                            }
                            return $parent_name;
                        },
                    ],
                    [
                        'attribute' => 'status',
                        'value' => function ($model) {
                            $status = "";
                            if ($model->status == ProductCategory::STATUS_PENDING_APPROVAL) {
                                $status = "Pending Approval";
                            } elseif ($model->status == ProductCategory::STATUS_APPROVE) {
                                $status = "Approved";
                            } elseif ($model->status == ProductCategory::STATUS_DECLINE) {
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
    function productcategorymodal(id) {
        $('#productcategorymodal_' + id).modal('show');
    }
</script>