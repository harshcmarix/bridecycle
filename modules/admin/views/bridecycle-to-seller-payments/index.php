<?php

use app\models\BridecycleToSellerPayments;
use app\modules\admin\widgets\GridView;
use kartik\editable\Editable;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\models\search\BridecycleToSellerPaymentsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


$this->title = 'Bridecycle To Seller Payments';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="career-index box box-primary">
    <div class="box-body table-responsive admin_list hotel_list dataTables_wrapper form-inline dt-bootstrap">
        <?php
        $gridColumns = [];
        echo GridView::widget([
            'id' => 'bc_to_seller_payments-grid',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'kartik\grid\SerialColumn'],
                [
                    'format' => ['raw'],
                    'attribute' => 'order_id',
                    'value' => function ($model) {
                        $orderLink = Html::a($model->order_id, \yii\helpers\Url::to(['order/view?id=' . $model->order_id . "&pageType=seller"]), ['class' => '']);
                        return $orderLink;
                    },
                    'header' => '',
                ],
                [
                    'attribute' => 'order_item_id',
                    'value' => function ($model) {
                        $orderItemName = '';
                        if ($model instanceof BridecycleToSellerPayments && !empty($model->orderItem) && $model->orderItem instanceof \app\models\OrderItem && !empty($model->orderItem->product) && $model->orderItem->product instanceof \app\models\Product) {
                            $orderItemName = $model->orderItem->product->name;
                        }
                        return $orderItemName;
                    },
                    'header' => 'Order Item',
                ],
                [
                    'format' => ['raw'],
                    'attribute' => 'seller_id',
                    'value' => function ($model) {
                        $seller = '-';
                        if ($model instanceof BridecycleToSellerPayments && !empty($model->seller) && $model->seller instanceof \app\modules\api\v2\models\User) {
                            $seller = Html::a($model->seller->first_name . " " . $model->seller->last_name, \yii\helpers\Url::to(['user/view?id=' . $model->seller->id . "&pageType=seller"]), ['class' => '']);
                        }
                        return $seller;
                    },
                    'header' => '',
                ],
                [
                    'format' => ['raw'],
                    'attribute' => 'buyer_id',
                    'value' => function ($model) {
                        $buyer = '-';
                        if ($model instanceof BridecycleToSellerPayments && !empty($model->order->user) && $model->order->user instanceof \app\modules\admin\models\User) {
                            $buyer = Html::a($model->order->user->first_name . " " . $model->order->user->last_name, \yii\helpers\Url::to(['user/view?id=' . $model->order->user->id . "&pageType=seller"]), ['class' => '']);
                        }
                        return $buyer;
                    },
                    'header' => 'Buyer',
                ],
                [
                    'attribute' => 'amount',
                    'value' => function ($model) {
                        $amount = '';
                        if ($model instanceof BridecycleToSellerPayments) {
                            $amount = Yii::$app->formatter->asCurrency($model->amount);
                        }
                        return str_replace('.',',',$amount);
                    },
                    'header' => 'Total Amount <br> (Product Price <br> + Tax <br> + Shipping)',
                ],
                [
                    'value' => function ($model) {
                        $bridecycleAmount = 0.0;
                        if ($model instanceof BridecycleToSellerPayments) {
                            $bridecycleAmount = $model->getBrideEarning($model->product_price);
                        }
                        return str_replace('.',',',Yii::$app->formatter->asCurrency($bridecycleAmount));
                    },
                    'header' => 'BrideCycle<br>Earning',
                ],
                [
                    'value' => function ($model) {
                        $bridecycleEarningAmount = 0.0;
                        if ($model instanceof BridecycleToSellerPayments) {
                            $bridecycleEarningAmount = $model->getBrideEarning($model->product_price);
                        }
                        return str_replace('.',',',Yii::$app->formatter->asCurrency(($model->amount - $bridecycleEarningAmount)));
                    },
                    'header' => 'Seller<br>Earning',
                ],
                [
                    'attribute' => 'status',
                    'value' => function ($model) {
                        $status = "Pending";
                        if ($model instanceof BridecycleToSellerPayments && $model->status == BridecycleToSellerPayments::STATUS_COMPLETE) {
                            $status = "Completed";
                        }
                        return $status;
                    },
                    'filter' => [BridecycleToSellerPayments::STATUS_PENDING => 'Pending', BridecycleToSellerPayments::STATUS_COMPLETE => "Completed"],
                    'filterType' => GridView::FILTER_SELECT2,
                    'filterWidgetOptions' => [
                        'options' => ['prompt' => 'Select'],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ],
                    'header' => '',
                ],
                [
                    'format' => ['raw'],
                    'value' => function ($model) {
                        if ($model instanceof BridecycleToSellerPayments && $model->status == BridecycleToSellerPayments::STATUS_PENDING) {
                            $isPaymentDone = "<button type='button' title='BrideCycle to seller payment status change if payment done by BrideCycle' class='btn btn-sm bc_to_seller_payment_payment-update' data-key='$model->id'>Payment Done?</button>";
                        } else {
                            $isPaymentDone = "<button type='button' title='Seller payment done from BrideCycle' class='btn bc_to_seller_payment_payment-done disabled' data-key='$model->id'><strong>Yes</strong></button>";
                        }
                        return $isPaymentDone;
                    },
                    'header' => 'Is Payment Done?',
                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'template' => '{view}   ',
                    'width' => '12%'
                ],
            ],

            'pjax' => true, // pjax is set to always true for this demo
            // set your toolbar
            'toolbar' => [
                [
                    'content' =>
                        Html::button('<i class="fa fa-refresh"> Reset </i>', [
                            'class' => 'btn btn-basic',
                            'title' => 'Reset Filter',
                            'onclick' => "window.location.href = '" . Url::to(['bridecycle-to-seller-payments/index']) . "';",
                        ]),
                    'options' => ['class' => 'btn-group mr-2']
                ],

                '{toggleData}',
            ],
            'toggleDataContainer' => ['class' => 'btn-group mr-2'],
            // parameters from the demo form
            'bordered' => true,
            'striped' => true,
            'condensed' => true,
            'responsive' => false,
            'panel' => [
                'type' => GridView::TYPE_DEFAULT,
            ],
            'persistResize' => false,
            'toggleDataOptions' => ['minCount' => 10],
            'itemLabelSingle' => 'bridecycle to seller payment',
            'itemLabelPlural' => 'bridecycle to seller payments'
        ]);
        ?>
    </div>
</div>

<!-- include modal popup start -->
<?php echo $this->render('bc_to_seller_payment_done_modal', ['model' => $modelUpdate]) ?>
<!-- include modal popup end -->

<script>
    function clearFilter(element) {
        element.previousSibling.value = '';
        var e = $.Event('keyup');
        e.which = 65;
        $(element).prev().trigger(e);
    }

    $('document').ready(function () {
        $('input[type=text]').after('<i class="fa fa-times" onclick="clearFilter(this)"></i>');

        var input;
        var submit_form = false;
        var filter_selector = '#bc_to_seller_payments-grid-filters input';
        var isInput = true;

        $('select').on('change', function () {
            isInput = false;
        });

        $('input').on('keypress', function () {
            isInput = true;
        });

        $("body").on('beforeFilter', "#bc_to_seller_payments-grid", function (event) {
            if (isInput) {
                return submit_form;
            }
        });

        $("body").on('afterFilter', "#bc_to_seller_payments-grid", function (event) {
            if (isInput) {
                submit_form = false;
            }
        });

        $(document)
            .off('keydown.yiiGridView change.yiiGridView', filter_selector)
            .on('keyup', filter_selector, function (e) {
                input = $(this).attr('name');
                var keyCode = e.keyCode ? e.keyCode : e.which;
                if ((keyCode >= 65 && keyCode <= 90) || (keyCode >= 48 && keyCode <= 57) || (keyCode >= 96 && keyCode <= 105) || (keyCode >= 186 && keyCode <= 192) || (keyCode >= 106 && keyCode <= 111) || (keyCode >= 219 && keyCode <= 222) || keyCode == 8 || keyCode == 32) {
                    if (submit_form === false) {
                        submit_form = true;
                        $("#bc_to_seller_payments-grid").yiiGridView("applyFilter");
                    }
                }
            })
            .on('pjax:success', function () {
                if (isInput) {
                    var i = $("[name='" + input + "']");
                    var val = i.val();
                    i.focus().val(val);

                    var searchInput = $(i);
                    if (searchInput.length > 0) {
                        var strLength = searchInput.val().length * 2;
                        searchInput[0].setSelectionRange(strLength, strLength);
                    }

                    if ($('thead td i').length == 0) {
                        $('input[type=text]').after('<i class="fa fa-times" onclick="clearFilter(this)"></i>');
                    }

                    $('.pagination').find('li a').on('click', function () {
                        setTimeout(function () {
                            $(document).scrollTop($(document).innerHeight());
                        }, 200);
                    })
                }
            });

        //select box filter
        var select;
        var submit_form = false;
        var select_filter_selector = '#bc_to_seller_payments-grid-filters select';
        var isSelect = true;

        $('select').on('change', function () {
            isSelect = true;
        });
        $('input').on('keypress', function () {
            isSelect = false;
        });
        $("body").on('beforeFilter', "#bc_to_seller_payments-grid", function (event) {
            if (isSelect) {
                return submit_form;
            }
        });
        $("body").on('afterFilter', "#bc_to_seller_payments-grid", function (event) {
            if (isSelect) {
                submit_form = false;
            }
        });

        $(document)
            .off('keydown.yiiGridView change.yiiGridView', select_filter_selector)
            .on('change', select_filter_selector, function (e) {
                select = $(this).attr('name');
                if (submit_form === false) {
                    submit_form = true;
                    $("#bc_to_seller_payments-grid").yiiGridView("applyFilter");
                }
            })
            .on('pjax:success', function () {
                var i = $("[name='" + input + "']");
                var val = i.val();
                i.focus().val(val);

                var searchInput = $(i);
                if (searchInput.length > 0) {
                    var strLength = searchInput.val().length * 2;
                    searchInput[0].setSelectionRange(strLength, strLength);
                }

                if (isSelect) {
                    if ($('thead td i').length == 0) {
                        $('input[type=text]').after('<i class="fa fa-times" onclick="clearFilter(this)"></i>');
                    }

                    $('.pagination').find('li a').on('click', function () {
                        setTimeout(function () {
                            $(document).scrollTop($(document).innerHeight());
                        }, 200);
                    })
                }
            });
    });

    $('.pagination').find('li a').on('click', function () {
        setTimeout(function () {
            $(document).scrollTop($(document).innerHeight());
        }, 200);
    });

    $(document).on('click', '.bc_to_seller_payment_payment-update', function (e) {
        e.preventDefault();
        var id = $(this).attr('data-key');
        krajeeDialog.confirm("Are you sure the seller's payment has been done?", function (out) {
            if (out) {
                // alert('Yes'); // or do something on confirmation
                var formAction = "<?php echo Url::to(['bridecycle-to-seller-payments/update', 'id' => ""]) ?>" + id;
                $('#bc_to_selle_payment_complete_with_comment-Modal').modal('show');
                var form = $('#bc_to_seller_payment-update-frm');
                form.attr('action', formAction);
            }
        });
    });

    $(document).on('click', '#btn-bc_to_seller_payment-update-form-submit', function (e) {
        e.preventDefault();
        var form = $('#bc_to_seller_payment-update-frm');
        $(this).attr("disabled", true);
        $("#btn-bc_to_seller_payment-update-form-cancel").attr("disabled", true);
        $.ajax({
            'url': form.attr('action'),
            "method": "POST",
            'data': form.serialize(),
            'dataType': "json",
            success: function (result) {
                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result);
                    $(this).attr("disabled", false);
                    $("#btn-bc_to_seller_payment-update-form-cancel").attr("disabled", false);
                }
            },
            fail: function (result) {
                alert(result);
                $(this).attr("disabled", false);
                $("#btn-bc_to_seller_payment-update-form-cancel").attr("disabled", false);
                return false;
            }
        });
    });
</script>