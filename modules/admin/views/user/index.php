<?php

use \app\modules\admin\widgets\GridView;
use kartik\editable\Editable;
use kartik\select2\Select2;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Url;

//use yii\grid\GridView;

use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\search\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="career-index box box-primary">
    <div class="box-body admin_list hotel_list dataTables_wrapper form-inline dt-bootstrap">
        <?php
        $gridColumns = [
            // [
            //     'attribute' => 'id',
            //     'value' => function ($model) {
            //         return $model->id;
            //     },
            //     'header' => 'User ID',
            //     'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
            // ],
            ['class' => 'kartik\grid\SerialColumn'],
            [
                'attribute' => 'first_name',
                'value' => function ($model) {
                    return $model->first_name;
                },
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
            ],
            [
                'attribute' => 'last_name',
                'value' => function ($model) {
                    return $model->last_name;
                },
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
            ],
            [
                'attribute' => 'email',
                'value' => function ($model) {
                    return $model->email;
                },
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
            ],
            [
                'format' => ['raw'],
                'enableSorting' => false,
                'filter' => false,
                'attribute' => 'profile_picture',
                'value' => function ($model) {
                    $image_path = "";
                    if (!empty($model->profile_picture) && file_exists(Yii::getAlias('@profilePictureThumbRelativePath') . '/' . $model->profile_picture)) {
                        $image_path = Yii::getAlias('@profilePictureThumbAbsolutePath') . '/' . $model->profile_picture;
                    } else {
                        $image_path = Yii::getAlias('@uploadsAbsolutePath') . '/no-image.jpg';
                    }
                    Modal::begin([
                        'id' => 'contentmodal_' . $model->id,
                        'header' => '<h3>Profile Picture</h3>',
                        'size' => Modal::SIZE_DEFAULT
                    ]);

                    echo Html::img($image_path, ['width' => '570']);

                    Modal::end();
                    $contentmodel = "contentmodel('" . $model->id . "');";
                    return Html::img($image_path, ['alt' => 'some', 'class' => 'your_class', 'onclick' => $contentmodel, 'height' => '50px', 'width' => '50px']);
                },
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important'],
            ],
            [
                'attribute' => 'mobile',
                'value' => function ($model) {
                    return (!empty($model) && !empty($model->mobile)) ? $model->mobile : "-";
                },
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
            ],
            [
                'attribute' => 'user_type',
                'value' => function ($data) {
                    if ($data->user_type == \app\modules\admin\models\User::USER_TYPE_ADMIN) {
                        return 'admin';
                    } elseif ($data->user_type == \app\modules\admin\models\User::USER_TYPE_SUB_ADMIN) {
                        return 'sub admin';
                    } elseif ($data->user_type == \app\modules\admin\models\User::USER_TYPE_NORMAL_USER) {
                        return 'normal user';
                    }
                },
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
            ],
            [
                'attribute' => 'is_shop_owner',
                'filter' => $searchModel->isShopOwner,
                'filterType' => GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'options' => ['prompt' => 'Select'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ],
                'value' => function ($data) {
                    return isset($data->isShopOwner[$data['is_shop_owner']]) ? $data->isShopOwner[$data['is_shop_owner']] : '-';
                },
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style', 'style' => 'text-align: center !important']
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
            ],
        ];

        echo GridView::widget([
            'id' => 'user-grid',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => $gridColumns, // check the configuration for grid columns by clicking button above
            'containerOptions' => ['style' => 'overflow: auto'], // only set when $responsive = false
            'headerRowOptions' => ['class' => 'kartik-sheet-style'],
            'filterRowOptions' => ['class' => 'kartik-sheet-style'],
            'pjax' => true, // pjax is set to always true for this demo
            'toolbar' => [
                [
                    'content' =>
                    Html::button('<i class="fa fa-plus-circle"> Add User </i>', [
                        'class' => 'btn btn-success',
                        'title' => 'Add User',
                        'onclick' => "window.location.href = '" . Url::to(['user/create']) . "';",
                    ]),
                    'options' => ['class' => 'btn-group mr-2']
                ],
                [
                    'content' =>
                    Html::button('<i class="fa fa-refresh"> Reset </i>', [
                        'class' => 'btn btn-basic',
                        'title' => 'Reset Filter',
                        'onclick' => "window.location.href = '" . Url::to(['user/index']) . "';",
                    ]),
                    'options' => ['class' => 'btn-group mr-2']
                ],
                '{toggleData}',
            ],
            'toggleDataContainer' => ['class' => 'btn-group mr-2'],
            'bordered' => true,
            'striped' => true,
            'condensed' => true,
            'responsive' => true,
            'panel' => [
                'type' => GridView::TYPE_DEFAULT,
                //'heading' => 'User',
            ],
            'persistResize' => false,
            'toggleDataOptions' => ['minCount' => 10],
            'itemLabelSingle' => 'user',
            'itemLabelPlural' => 'Users',
        ]);
        ?>
    </div>
</div>

<script type="text/javascript">
    function contentmodel(id) {
        $('#contentmodal_' + id).modal('show');
    }

    $('document').ready(function(){
        var input;
        var submit_form = false;
        var filter_selector = '#user-grid-filters input';

        $("body").on('beforeFilter', "#user-grid" , function(event) {
            return submit_form;
        });

        $("body").on('afterFilter', "#user-grid" , function(event) {
            submit_form = false;
        });

        $(document)
        .off('keydown.yiiGridView change.yiiGridView', filter_selector)
        .on('keyup', filter_selector, function(e) {
            input = $(this).attr('name');
            var keyCode = e.keyCode ? e.keyCode : e.which;
            if ((keyCode >= 65 && keyCode <= 90) || (keyCode >= 48 && keyCode <= 57) || (keyCode >= 96 && keyCode <= 105) || (keyCode >= 186 && keyCode <= 192) || (keyCode >= 106 && keyCode <= 111) || (keyCode >= 219 && keyCode <= 222) || keyCode == 8 || keyCode == 32) {
                if (submit_form === false) {
                    submit_form = true;
                    $("#user-grid").yiiGridView("applyFilter");
                }
            }
        })
        .on('pjax:success', function() {
            var i = $("[name='"+input+"']");
            var val = i.val();
            i.focus().val(val);

            var searchInput = $(i);
            var strLength = searchInput.val().length * 2;
            searchInput[0].setSelectionRange(strLength, strLength);
        });
    });
</script>