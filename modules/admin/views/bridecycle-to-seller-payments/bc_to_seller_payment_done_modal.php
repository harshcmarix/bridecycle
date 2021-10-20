<?php

use yii\widgets\ActiveForm;

/* @var $form yii\widgets\ActiveForm */
/* @var $model app\models\BridecycleToSellerPayments */
?>
<!-- Modal -->
<div class="modal fade" id="bc_to_selle_payment_complete_with_comment-Modal" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="exampleModalLabel">Seller's Payment From
                    BrideCycle
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </h3>
            </div>
            <div class="modal-body">

                <div class="bridecycle-to-seller-payments-form">

                    <?php $form = ActiveForm::begin(['id' => 'bc_to_seller_payment-update-frm', 'action' => "#", 'options' => ['enctype' => 'multipart/form-data', 'autocomplete' => 'off'],]); ?>

                    <?= $form->field($model, 'note_content')->textarea(['rows' => 6]) ?>

                    <?php ActiveForm::end(); ?>

                </div>

                <!--                <form id="" name="" method="post" action="#" enctype="multipart/form-data" autocomplete="off">-->
                <!--                    <div class="form-group">-->
                <!--                        <div class="form-control">-->
                <!---->
                <!--                        </div>-->
                <!--                    </div>-->
                <!--                </form>-->
            </div>
            <div class="modal-footer">
                <button type="button" id="btn-bc_to_seller_payment-update-form-cancel" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" id="btn-bc_to_seller_payment-update-form-submit"
                        style="background-color: #8a9673; border-color:#8a9673; " class="btn btn-primary">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>